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
   *
   * @return array|null
   *   An array containing the formatted data for the weather, or null
   */
  public function getGeoData(string $city): ?array;

  /**
   * Get current weather data.
   *
   * @param string $api_key
   *   The API key from Weather service.
   * @param string $city
   *   Name of the city.
   *
   * @return array|null
   *   An array containing the formatted data for the weather, or null
   */
  public function getWeatherData(string $api_key, string $city): ?array;
  public function getLocationByIP(): ?array;

}
