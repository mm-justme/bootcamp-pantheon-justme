<?php

namespace Drupal\custom_reg\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;

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
    protected MailManagerInterface $mailManager,
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
    $message_enum = [
      'invalid' => $this->t('<p style="color:maroon">The Email address is not valid.</p>'),
      'exists' => $this->t('<p style="color:maroon">The Email address already exists.</p>'),
      'valid' => $this->t('<p style="color:forestgreen">The Email address is valid.</p>'),
    ];

    // Check email format by using EmailValidatorInterface.
    // Return TRUE if email is valid.
    $email_status = $this->emailValidator->isValid($email);
    $error_message = $email_status ? $message_enum['valid'] : $message_enum['invalid'];

    if (!$email_status) {
      $response->addCommand(new HtmlCommand('#email-status', $error_message));
      return $response;
    }

    // @todo needn't update \Drupal::entityQuery('user').
    // @todo Current check method will be deleted.
    // Provide search in the DB of the users.
    // Return TRUE if we found at least 1 user with corresponding email.
    $is_email_exists = (bool) \Drupal::entityQuery('user')
      ->accessCheck(FALSE)
      ->condition('mail', $email)
      ->range(0, 1)
      ->execute();

    $error_message = $is_email_exists ? $message_enum['exists'] : $message_enum['valid'];
    $response->addCommand(new HtmlCommand('#email-status', $error_message));
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $email = $form_state->getValue('email');
    $user_name = $form_state->getValue('username');
    $email_params = [
      'username' => $user_name,
    ];

    // Provide sending email by using MailManagerInterface.
    // Look at the custom_reg.module file, which contain differance messages.
    $this->mailManager->mail('custom_reg', 'custom_reg.test', $email, 'en', $email_params, $reply = NULL, $send = TRUE);

    $this->messenger()->addStatus($this->t('The message has been sent.'));
  }

}
