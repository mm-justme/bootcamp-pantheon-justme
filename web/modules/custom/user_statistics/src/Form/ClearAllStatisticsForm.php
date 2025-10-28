<?php

namespace Drupal\user_statistics\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a method to delete all users statistic records.
 */
class ClearAllStatisticsForm extends ConfirmFormBase implements ContainerInjectionInterface {
  protected LoggerInterface $logger;

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): string {
    return $this->t('Are you sure you want to clear all user statistics?');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl(): Url {
    // On cancel, we return user to the statistics page.
    return Url::fromRoute('entity.user_statistics.collection');
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'user_statistics_clear_all';
  }

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
  ) {
    $this->logger = $loggerChannelFactory->get('user_statistics');
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {

      $storage = $this->entityTypeManager->getStorage('user_statistics');

      $records = $this->entityTypeManager
        ->getStorage('user_statistics')
        ->getQuery()
        ->accessCheck(FALSE)
        ->execute();

      if (empty($records)) {
        $this->messenger()->addStatus($this->t('There are no records to clear'));
        $form_state->setRedirectUrl($this->getCancelUrl());
        return;
      }

      $entities = $storage->loadMultiple($records);
      $storage->delete($entities);
    }
    catch (\Throwable $e) {
      $this->messenger()->addStatus($this->t('Failed to clear user_statistics: @msg', ['@msg' => $e->getMessage()]));
      $this->logger->error($this->t('An error occurred while clearing records.'));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
    $this->messenger()->addStatus($this->t('All records have been cleared.'));

  }

}
