<?php

namespace Drupal\show_weather;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Weather API client.
 */
class WeatherClient implements WeatherClientInterface {

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a download process plugin.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory service.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('show_weather');
  }

  /**
   * {@inheritDoc}
   */
  public function getWeatherData(string $city, string $api_key): ?array {
    $url_geo = 'https://api.openweathermap.org/geo/1.0/direct';
    $url_data = 'https://api.openweathermap.org/data/2.5/weather';
    $lat = NULL;
    $lon = NULL;
    $weather_data = [];

    try {
      // Make request to receive $lat and $lon.
      $get_geo = $this->httpClient->request('GET', $url_geo,
        [
          'query' => [
            'q' => $city,
            'limit' => 1,
            'appid' => $api_key,
          ],
          'timeout' => 3,
        ]);
      $location = json_decode($get_geo->getBody(), TRUE);

      if (is_array($location) && !empty($location[0])) {
        $lat = $location[0]["lat"] ?? NULL;
        $lon = $location[0]["lon"] ?? NULL;
      }
    }
    catch (GuzzleException $e) {
      $this->logger->error('Weather fetch is failed: @msg', ['@msg' => $e->getMessage()]);
    }
    // Check received data of the $lat and $lon.
    // And request info about the weather acc. to the $lat and $lon.
    if (!is_null($lat) && !is_null($lon)) {
      try {
        $get_weather = $this->httpClient->request('GET', $url_data,
          [
            'query' => [
              'lat' => $lat,
              'lon' => $lon,
              'appid' => $api_key,
              'units' => 'metric',
            ],
            'timeout' => 3,
          ]);
        $weather = json_decode($get_weather->getBody(), TRUE);
        $weather_data = $weather;
      }
      catch (GuzzleException $e) {
        $this->logger->error('Geocoding failed: @msg', ['@msg' => $e->getMessage()]);
      }
    }

    return $weather_data;
  }

}
