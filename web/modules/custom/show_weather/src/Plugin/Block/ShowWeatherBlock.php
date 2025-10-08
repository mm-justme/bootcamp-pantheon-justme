<?php

namespace Drupal\show_weather\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\show_weather\WeatherClientInterface;
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
   *
   * @var \Drupal\show_weather\WeatherClientInterface
   */
  protected $weatherClient;

  /**
   * Constructs a download process plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The file system service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\show_weather\WeatherClientInterface $weather_client,
   *   The config factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ConfigFactoryInterface $configFactory,
    WeatherClientInterface $weather_client,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->weatherClient = $weather_client;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get(WeatherClientInterface::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $api_key = (string) $this->configFactory->get('show_weather.settings')->get('api_key') ?? '';
    $city = (string) $this->configFactory->get('show_weather.settings')->get('city') ?? 'Lutsk';

    // Check $api_key and $city.
    // Show URL to the settings page if we don`t have it.
    if ($api_key === '') {
      $url = Url::fromRoute('show_weather.settings')->toString();
      return [
        '#markup' => $this->t(
          'API key or city is missing. Click 
           <a href=":url">here</a> to add your configuration, please.', [':url' => $url]),
        '#cache' => ['max-age' => 0],
      ];
    }

    $weather = $this->weatherClient->getWeatherData($city, $api_key);
    $text = 'The Weather service unavailable so far.';
    $temp = $weather['main']['temp'] ?? NULL;
    $desc = $weather['weather'][0]['description'] ?? '';

    if ($temp !== NULL) {
      $text = $this->t('@city: @temp°C — @desc', [
        '@city' => $city,
        '@temp' => round($temp),
        '@desc' => $desc,
      ]);
    }

    return [
      '#markup' => $text,
      '#cache' => ['max-age' => 600],
    ];
  }

}
