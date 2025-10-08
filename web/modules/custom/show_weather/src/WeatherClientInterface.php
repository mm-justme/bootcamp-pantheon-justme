<?php

namespace Drupal\show_weather;

/**
 * Defines the interface for a weather API.
 */
interface WeatherClientInterface {

  /**
   * Get current weather data.
   *
   * @param string $city
   *   Name of the city.
   * @param string $api_key
   *   The API key from Weather service.
   *
   * @return array|null
   *   An array containing the formatted data for the weather, or null
   */
  public function getWeatherData(string $city, string $api_key): ?array;

}
