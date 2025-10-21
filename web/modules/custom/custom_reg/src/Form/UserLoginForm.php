<?php

declare(strict_types=1);

namespace Drupal\custom_reg\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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

  public function __construct(
    private Connection $db,
  ) {}

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('database'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
      '#placeholder' => $this->t('dimka-ua'),
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $password = $form_state->getValue('password');
    $username = $form_state->getValue('username');

//    $query = $this->db
//      ->select('custom_reg_users', 'c')
//      ->fields('c', ['username'])
//      ->condition('username', $username)
//      ->range(0, 1)
//      ->execute();



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
    $this->messenger()->addStatus($this->t('Test Login.'));
    $form_state->setRedirect('<front>');
  }

}
