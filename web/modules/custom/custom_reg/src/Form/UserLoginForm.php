<?php

declare(strict_types=1);

namespace Drupal\custom_reg\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Password\PasswordInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom_reg form.
 */
final class UserLoginForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'custom_reg_user_login';
  }

  /**
   * The Logger service.
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new UserLoginForm object.
   *
   * @param \Drupal\Core\Database\Connection $databaseService
   *   The database connection for interacting with custom tables.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger channel factory used to create a logger instance.
   * @param \Drupal\Core\Password\PasswordInterface $passwordService
   *   The password hashing service.
   */
  public function __construct(
    private readonly Connection $databaseService,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    private PasswordInterface $passwordService,
  ) {
    $this->logger = $loggerChannelFactory->get('custom_reg');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('database'),
      $container->get('logger.factory'),
      $container->get('password'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
      '#maxlenght' => 60,
      '#description' => $this->t('You can use 2-60 characters.'),
      '#placeholder' => $this->t('Examples: user-12, User_12'),
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#description' => $this->t('Min 6 characters.'),
    ];

    $form['login-status'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'login-status'],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Log in'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => 'login-modal-form',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /**
   * AJAX callback to check the login form.
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
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $error_message = $this->t('<p style="color:red">Username or password incorrect.</p>');

    // Show errors in the modal window.
    $errors = $form_state->getErrors();
    if ($errors) {
      $response->addCommand(new HtmlCommand('#login-status', $error_message));
      return $response;
    }

    $response->addCommand(new CloseModalDialogCommand());
    // Reload page.
    $response->addCommand(new RedirectCommand('/'));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $password = $form_state->getValue('password');
    $username = $form_state->getValue('username');
    // In the Login form we show only general error message.
    $error_message = $this->t('Username or password incorrect.');

    // 2–30 characters.Only letters (a–z, A–Z), numbers (0–9), hyphens (-)
    // and underscores (_). No spaces or other special characters
    if (!preg_match('/^[A-Za-z0-9_-]{2,30}$/', $username)) {
      $form_state->setErrorByName('user_name', $error_message);
      return;
    }
    // Minimum 6 characters. Must contain at least one letter and at least
    // one number.Permitted characters:Latin letters,numbers,@#%$!_-.
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@#%$!_-]{6,}$/', $password)) {
      $form_state->setErrorByName('Login', $error_message);
      return;
    }

    try {
      $user = $this->databaseService
        ->select('custom_reg_users', 'c')
        ->fields('c', ['username', 'password', 'uid'])
        ->condition('username', $username)
        ->range(0, 1)
        ->execute()
        ->fetchAssoc();

      if (!$user) {
        $form_state->setErrorByName('Login', $error_message);
        return;
      }

      if (!$this->passwordService->check($password, $user['password'])) {
        $form_state->setErrorByName('Login', $error_message);
        return;
      }

      setcookie('custom_reg_userId', $user['uid'], time() + 3600 * 3);

    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      $form_state->setErrorByName('username', $this->t('A technical error occurred. Please try again later.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if (!$form_state->getErrors()) {
      $this->messenger()->addStatus($this->t('Your account has been successfully logged in.'));
    }
  }

}
