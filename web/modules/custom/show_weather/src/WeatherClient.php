<?php

namespace Drupal\show_weather;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Weather API service.
 */
class WeatherClient implements WeatherClientInterface {

  /**
   * The logger interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a WeatherClient object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The Guzzle HTTP client service for making API requests.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory used to create a logger channel for the module.
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
  public function getGeoData(string $city): ?array {
    // $test = $this->httpClient->request('get','http://ip-api.com/json');
    //    $d = json_decode($test->getBody()->getContents(), TRUE);
    //    dd($d);
    $url_geo = 'https://api.openweathermap.org/geo/1.0/direct';
    $api_key = '06ab2d5eeae73540ec27071666893a72';
    $weather_data = [
      'is_city_exists' => FALSE,
      'lat' => NULL,
      'lon' => NULL,
    ];

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
      $geo = json_decode($get_geo->getBody(), TRUE);
      if (!empty($geo[0])) {
        $weather_data['lat'] = $geo[0]["lat"] ?? NULL;
        $weather_data['lon'] = $geo[0]["lon"] ?? NULL;
      }

      if ($weather_data['lat'] != NULL && $weather_data['lon'] != NULL) {
        $weather_data['is_city_exists'] = TRUE;
      }

    }
    catch (GuzzleException $e) {
      $this->logger->error('Weather fetch is failed: @msg', ['@msg' => $e->getMessage()]);
    }

    return $weather_data;
  }

  /**
   * {@inheritDoc}
   */
  public function getWeatherData($api_key, $city): ?array {
    $url_data = 'https://api.openweathermap.org/data/2.5/weather';
    $data = $this->getGeoData($city);
    $lat = $data['lat'];
    $lon = $data['lon'];
    $weather_data = NULL;
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
        if (!is_null($weather)) {
          $weather_data = $weather;
        }
      }
      catch (GuzzleException $e) {
        $this->logger->error('Fetch weather info is failed: @msg', ['@msg' => $e->getMessage()]);
      }
    }

    return $weather_data;
  }

  /**
   *
   */
  public function getLocationByIP(): ?array {
    $request = $this->httpClient->request('get', 'http://ip-api.com/json');
    $data = json_decode($request->getBody()->getContents(), TRUE);

    $city = $data['city'];
    $lat = $data['lat'];
    $lon = $data['lon'];

    return [
      'city' => $city,
      'lat' => $lat,
      'lon' => $lon,
    ];
  }

}
