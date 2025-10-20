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
    // 2–30 characters.Only letters (a–z, A–Z), numbers (0–9), hyphens (-)
    // and underscores (_). No spaces or other special characters
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
      '#maxlenght' => 60,
      '#description' => $this->t("You can use 2-30 characters, (a–z, A–Z), 
      numbers (0–9). Hyphens (-) and underscores (_). No spaces or other special characters"),
      '#placeholder' => $this->t('Examples: user-12, User_12'),
    ];

    $form['email'] = [
      '#type' => 'email',
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
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#min_length' => 6,
      '#description' => $this->t('Min 6 characters. Must contain at least one letter and at least
     one number. Permitted characters: Latin letters, numbers,@ # % $ ! _ - .'),
    ];

    $form['confirm_pass'] = [
      '#type' => 'password',
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
      '#type' => 'number',
      '#title' => $this->t('Age'),
      '#min' => 16,
      '#required' => FALSE,
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

    // Provides search in the DB custom_reg_users.
    // Return TRUE if we found at least 1 user with corresponding email.
    $is_email_exists = (bool) \Drupal::database()
      ->select('custom_reg_users', 'c')
      ->fields('c', ['uid'])
      ->condition('email', $email)
      ->range(0, 1)
      ->execute()
      ->fetchField();

    $error_message = $is_email_exists ? $message_enum['exists'] : $message_enum['valid'];
    $response->addCommand(new HtmlCommand('#email-status', $error_message));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $email = $form_state->getValue('email');
    $user_name = $form_state->getValue('username');
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_pass');
    $age = $form_state->getValue('confirm_pass');
    $country = $form_state->getValue('country');
    $about = $form_state->getValue('about');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Invalid email.Please try again.'));
    }
    // 2–30 characters.Only letters (a–z, A–Z), numbers (0–9), hyphens (-)
    // and underscores (_). No spaces or other special characters
    if (!preg_match('/^[A-Za-z0-9_-]{2,30}$/', $user_name)) {
      $form_state->setErrorByName('user_name', $this->t('Invalid username.Please try again.'));
    }
    // Minimum 6 characters. Must contain at least one letter and at least
    // one number.Permitted characters:Latin letters,numbers,@#%$!_-.
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@#%$!_-]{6,}$/', $password)) {
      $form_state->setErrorByName('password', $this->t('Invalid password.Please read the description of the field and try again.'));
    }
    elseif ($password !== $confirm_password) {
      $form_state->setErrorByName('confirm_pass', $this->t('Passwords do not match.'));
    }

    if ($form_state->getValue('add_info')) {
      // Only numbers from 2 to 120.
      if ($age === '' || !preg_match('/^(?:1[01][0-9]|[2-9][0-9]|120)$/',
          $age)) {
        $form_state->setErrorByName('age',
          $this->t('Please enter a valid age between 2 and 120.'));
      }
      // Only letters(any case),spaces,hyphens.Minimum 2, maximum 60 characters.
      if ($country !== '' && !preg_match('/^[A-Za-z\s-]{2,60}$/', $country)) {
        $form_state->setErrorByName('country',
          $this->t('Country name must be 2–60 letters (letters, spaces, or hyphens only).'));
      }
      // Text up to 500 characters. Must not contain HTML
      // (we will check it simply at </>).
      if ($about !== '') {
        if (strlen($about) > 500) {
          $form_state->setErrorByName('about',
            $this->t('About yourself must not exceed 500 characters.'));
        }
        elseif (preg_match('/[<>]/', $about)) {
          $form_state->setErrorByName('about',
            $this->t('HTML tags are not allowed in the About field.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $email = $form_state->getValue('email');
    $user_name = $form_state->getValue('username');
    $password = $form_state->getValue('password');
    $age = $form_state->getValue('age');
    $country = $form_state->getValue('country');
    $about = $form_state->getValue('about');

    // Change empty string to the NULL.
    $age = $age != '' ? $age : NULL;
    $country = $country != '' ? $country : NULL;
    $about = $about != '' ? $about : NULL;

    $email_params = [
      'username' => $user_name,
    ];

    // Error Handling.
    $txn = \Drupal::database()->startTransaction();

    try {
      // Registration a user in to the custom_reg_users table.
      \Drupal::database()->insert('custom_reg_users')->fields([
        'username' => $user_name,
        'email' => $email,
        'password' => \Drupal::service('password')->hash($password),
        'age' => $age ?? NULL,
        'country' => $country ?? NULL,
        'about' => $about ?? NULL,
        'created' => \Drupal::time()->getRequestTime(),
        'updated' => \Drupal::time()->getRequestTime(),
      ])
        ->execute();

      $this->mailManager->mail('custom_reg', 'custom_reg.test', $email, 'en', $email_params, $reply = NULL, $send = TRUE);

    }
    catch (\Exception $e) {
      $txn->rollBack();
      \Drupal::logger('custom_reg')->error($e->getMessage());
    }

    $this->messenger()->addStatus($this->t('User has been registered. The message has been sent to @email.', ['@email' => $email]));
  }

}
