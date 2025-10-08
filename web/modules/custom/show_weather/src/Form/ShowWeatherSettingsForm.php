<?php

namespace Drupal\show_weather\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\show_weather\WeatherClientInterface;

/**
 * {@inheritdoc}
 */
class ShowWeatherSettingsForm extends ConfigFormBase {

  /**
   * The WeatherClient service.
   *
   * @var \Drupal\show_weather\WeatherClientInterface
   */
  protected $weatherClient;
  private const SETTINGS = 'show_weather.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return self::SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [self::SETTINGS];
  }

  /**
   * Constructs a WeatherClient object.
   *
   * @param \Drupal\show_weather\WeatherClientInterface $weather_client
   *   The weather service.
   */
  public function __construct(WeatherClientInterface $weather_client) {
    $this->weatherClient = $weather_client;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get(WeatherClientInterface::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::SETTINGS);

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenWeatherMap API key'),
      '#default_value' => $config->get('api_key') ?? '',
      '#description' => $this->t('Create an API key at openweathermap.org and paste it here.'),
      '#required' => TRUE,
    ];
    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#default_value' => $config->get('city') ?? 'Lutsk',
      '#description' => $this->t('Lutsk - provided as default city'),
      '#required' => TRUE,
      '#placeholder' => 'Lutsk',
    ];
    $form['location'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Location by IP'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    $api_key = $form_state->getValue('api_key');
    $city = $form_state->getValue('city');
    $auto_location = $form_state->getValue('location');

    if (preg_match('/\d/', $city)) {
      $form_state->setErrorByName('city', $this->t('The City field cannot contain numbers. Please try again.'));
    }
    $city_checked = $this->weatherClient->getGeoData($city);
    if (!$city_checked['is_city_exists']) {
      $form_state->setErrorByName('city', $this->t('The City field is invalid. Please try again.'));
      return;
    }

    if (strlen($api_key) < 20) {
      $form_state->setErrorByName('api_key', $this->t('Invalid API key. Please try again.'));
      return;
    }
    // Make request to the weather API, return array, or empty array.
    $weather_data = $this->weatherClient->getWeatherData($api_key, $city);
    if (!is_array($weather_data) || empty($weather_data)) {
      $form_state->setErrorByName('city', $this->t('Invalid API key. The weather info is missing. Please try again.'));
    }

  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('api_key');
    $city = $form_state->getValue('city');

    $this->configFactory->getEditable(self::SETTINGS)
      ->set('api_key', $api_key)
      ->set('city', $city)
      ->save();

    $this->config(self::SETTINGS)
      ->set('api_key', $api_key)
      ->set('city', $city)
      ->save();

    $this->messenger()->addMessage($this->t('Configuration has been saved.'));
  }

}
