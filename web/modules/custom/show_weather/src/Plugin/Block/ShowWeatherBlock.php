<?php

namespace Drupal\show_weather\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides a "Weather" block.
 *
 * @Block(
 *   id = "weather_block",
 *   admin_label = @Translation("Show Weather")
 * )
 */
class ShowWeatherBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $apiKey = '92d717b1083712802aa9804c4ee82f0f';
    $city = 'Lutsk';
    $client = \Drupal::httpClient();

    try {
      $get_location = $client->get('https://api.openweathermap.org/geo/1.0/direct',
        [
          'query' => [
            'q' => $city,
            'limit' => 1,
            'appid' => $apiKey,
          ],
          'timeout' => 3,
        ]);
      $location = json_decode($get_location->getBody(), TRUE);

      if (is_array($location) && !empty($location[0])) {
        $lat = $location[0]["lat"] ?? NULL;
        $lon = $location[0]["lon"] ?? NULL;
      }
    }
    catch (GuzzleException $e) {
      \Drupal::logger('show_weather')
        ->error('Geocoding failed: @msg', ['@msg' => $e->getMessage()]);
    }

    if (!is_null($lat) && !is_null($lon)) {
      try {
        $get_weather = $client->get('https://api.openweathermap.org/data/2.5/weather',
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
        \Drupal::logger('show_weather')
          ->error('Geocoding failed: @msg', ['@msg' => $e->getMessage()]);
      }
    }

    return [
      '#markup' => $text,
      '#cache' => ['max-age' => 600],
    ];
  }

}
