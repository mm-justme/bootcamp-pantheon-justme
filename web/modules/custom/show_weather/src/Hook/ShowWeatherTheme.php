<?php

namespace Drupal\show_weather\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for weather.
 */
class ShowWeatherTheme {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'weather_block' => [
        'template' => 'weather-block',
        'variables' => [
          'weather_city' => NULL,
          'weather_temp' => NULL,
          'weather_desc' => NULL,
          'weather_message' => NULL,
        ],
      ],
    ];
  }

}
