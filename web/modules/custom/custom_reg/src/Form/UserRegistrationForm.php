<?php

namespace Drupal\custom_reg\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Custom registration user form.
 */
final class UserRegistrationForm extends FormBase {
  use AutowireTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'custom_reg.settings';
  }

  public function __construct(
    protected EmailValidatorInterface $emailValidator,
  ) {}

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
      // Add ajax to the field.
      '#ajax' => [
        // Call method.
        'callback' => '::emailAjaxValidate',
        // Trigger which say start my method, blur - means on focus.
        'event' => 'blur',
        // Element in which we'll return the result.
        'wrapper' => 'email-status',
        // Provide the UI,visible, progress bar.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Checking email...'),
        ],
      ],
    ];

    $form['email_status'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'email-status'],
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
   * AJAX callback to validate an email address.
   *
   * This method is triggered by an AJAX event on the email input field.
   * It uses the core EmailValidator service to verify the email format
   * and returns a visual status message via AjaxResponse.
   *
   * @param array $form
   *   The form structure array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response containing an HtmlCommand that updates the
   *   `#email-status` element with a validation message.
   */
  public function emailAjaxValidate(
    array &$form,
    FormStateInterface $form_state,
  ): AjaxResponse {
    $response = new AjaxResponse();

    $email = $form_state->getValue('email');
    $msg = [
      'invalid' => $this->t('<p style="color:maroon">The Email address is not valid.</p>'),
      'exists' => $this->t('The Email address already exists.'),
      'valid' => $this->t('<p style="color:forestgreen">The Email address is valid.</p>'),
    ];

    // Check email format by using
    // Drupal\Component\Utility\EmailValidatorInterface.
    $email_status = $this->emailValidator->isValid($email);
    $text = $email_status ? $msg['valid'] : $msg['invalid'];

    // @todo create checking method for email. Check if exist one in DB
    $response->addCommand(new HtmlCommand('#email-status', $text));
    return $response;
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
    //    dump('test');.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
