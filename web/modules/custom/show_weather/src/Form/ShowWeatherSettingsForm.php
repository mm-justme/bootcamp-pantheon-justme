<?php

namespace Drupal\show_weather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
final class ShowWeatherSettingsForm extends ConfigFormBase {
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
      '#default_value' => $config->get('city') ?? '',
      '#description' => $this->t('Lutsk - provided as default city'),
      '#required' => FALSE,
      '#placeholder' => 'Lutsk',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    $api_key = $form_state->getValue('api_key');
    if (strlen($api_key) < 20) {
      $form_state->setErrorByName('api_key', $this->t('Invalid API key. Please try again.'));
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
