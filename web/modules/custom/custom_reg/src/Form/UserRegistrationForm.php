<?php

namespace Drupal\custom_reg\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Custom registration user form.
 */
final class UserRegistrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'custom_reg.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];

    $form['confirm_pass'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Confirm Password'),
      '#required' => TRUE,
    ];

    $form['add_info'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Additional information'),
      '#default_value' => FALSE,
    ];

    // Display fields age, country and about only
    // if the field add_info is checked.
    $states = [
      'visible' => [
        ':input[name="add_info"]' => ['checked' => TRUE],
      ],
    ];

    $form['age'] = [
      '#states' => $states,
      '#type' => 'textfield',
      '#title' => $this->t('Age'),
    ];

    $form['country'] = [
      '#states' => $states,
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
    ];

    $form['about'] = [
      '#states' => $states,
      '#type' => 'textfield',
      '#title' => $this->t('About yourself'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Register'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
