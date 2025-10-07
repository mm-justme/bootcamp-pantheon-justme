<?php

namespace Drupal\show_weather\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "Weather" block.
 */
#[Block(
   id: "weather_block",
   admin_label: new TranslatableMarkup("Show Weather"),
)]
class ShowWeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a download process plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The file system service.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ClientInterface $httpClient,
    LoggerChannelFactoryInterface $loggerFactory,
    private readonly ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $loggerFactory->get('show_weather');

  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('logger.factory'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $apiKey = (string) $this->configFactory->get('show_weather.settings')->get('api_key');
    // Check $apiKey.
    // Show URL to the settings page if we don`t have it.
    if ($apiKey === '') {
      $url = Url::fromRoute('show_weather.settings')->toString();
      return [
        '#markup' => $this->t('No API key set. Add it on the <a href=":url">Show Weather settings</a> page.', [':url' => $url]),
        '#cache' => [
          'max-age' => 0,
          'tags' => ['config:show_weather.settings'],
        ],
      ];
    }

    $city = 'Lutsk';
    $lat = NULL;
    $lon = NULL;
    $text = 'The Weather service unavailable so far.';

    try {
      // Make request to receive $lat and $lon.
      $get_location = $this->httpClient->request('get', 'https://api.openweathermap.org/geo/1.0/direct',
        [
          'query' => [
            'q' => $city,
            'limit' => 1,
            'appid' => $apiKey,
          ],
          'timeout' => 3,
        ]);
      $location = json_decode($get_location->getBody(), TRUE);
      // Check if data is correct type and it isn't empty.
      if (is_array($location) && !empty($location[0])) {
        $lat = $location[0]["lat"] ?? NULL;
        $lon = $location[0]["lon"] ?? NULL;
      }
    }
    catch (GuzzleException $e) {
      $this->logger->error('Geocoding failed: @msg', ['@msg' => $e->getMessage()]);
    }
    // Check received data of the $lat and $lon.
    // And request info about the weather acc. to the $lat and $lon.
    if (!is_null($lat) && !is_null($lon)) {
      try {
        $get_weather = $this->httpClient->request('get', 'https://api.openweathermap.org/data/2.5/weather',
          [
            'query' => [
              'lat' => $lat,
              'lon' => $lon,
              'appid' => $apiKey,
              'units' => 'metric',
            ],
            'timeout' => 3,
          ]);
        $weather = json_decode($get_weather->getBody(), TRUE);
        $temp = $weather['main']['temp'] ?? NULL;
        $desc = $weather['weather'][0]['description'] ?? '';

        if ($temp !== NULL) {
          $text = $this->t('@city: @temp°C — @desc', [
            '@city' => $city,
            '@temp' => round($temp),
            '@desc' => $desc,
          ]);

        }
      }
      catch (GuzzleException $e) {
        $this->logger->error('Geocoding failed: @msg', ['@msg' => $e->getMessage()]);
      }
    }
    return [
      '#markup' => $text,
      '#cache' => ['max-age' => 600],
    ];
  }

}
