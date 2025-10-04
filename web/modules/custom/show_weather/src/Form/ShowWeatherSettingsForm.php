<?php

namespace Drupal\show_weather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class ShowWeatherSettingsForm extends ConfigFormBase {
  private const SETTINGS = 'show_weather.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'show_weather_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [self::SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(self::SETTINGS);

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenWeatherMap API key'),
      '#default_value' => $config->get('api_key') ?? '',
      '#description' => $this->t('Create an API key at openweathermap.org and paste it here.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory->getEditable(self::SETTINGS)
      ->set('api_key', trim((string) $form_state->getValue('api_key')))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
