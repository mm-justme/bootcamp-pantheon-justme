<?php

namespace Drupal\user_statistics\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a method to delete all user own records.
 */
class ClearOwnStatisticsConfirmForm extends ConfirmFormBase implements ContainerInjectionInterface {

  /**
   * The logger service variable.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): string {
    return $this->t('Are you sure you want to clear all your records?');
  }

  /**
   * {@inheritDoc}
   */
  public function getDescription(): string {
    return $this->t('This will permanently remove records that belong to your account.');
  }

  /**
   * {@inheritDoc}
   */
  public function getConfirmText(): string {
    return $this->t('Clear my stats');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl(): Url {
    // On cancel, we return user to the statistics page.
    return Url::fromRoute('user_statistics.collection.own');
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'user_statistics_clear_own_confirm';
  }

  /**
   * Constructs of a ClearAllStatisticsConfirmForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger factory service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    private readonly AccountInterface $currentUser,
  ) {
    $this->logger = $loggerChannelFactory->get('user_statistics');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $uid = $this->currentUser->id();
      $storage = $this->entityTypeManager->getStorage('user_statistics');

      $users_records = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('uid', $uid)
        ->execute();

      if (empty($users_records)) {
        $this->messenger()->addStatus($this->t('You have no records to clear'));
        $form_state->setRedirectUrl($this->getCancelUrl());
        return;
      }

      $entities = $storage->loadMultiple($users_records);
      $storage->delete($entities);
    }
    catch (\Throwable $e) {
      $this->messenger()->addStatus($this->t('Failed to clear your records: @msg', ['@msg' => $e->getMessage()]));
      $this->logger->error($this->t("An error occurred while clearing user's records."));
    }
    $form_state->setRedirectUrl($this->getCancelUrl());
    $this->messenger()->addStatus($this->t("All users's records have been cleared."));
  }

}
