<?php

namespace Drupal\user_statistics;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler fo the user_statistics entity.
 *
 * @see \Drupal\user_statistics\Entity\UserStatistics
 */
class UserStatisticsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(
    EntityInterface $entity,
    $operation,
    AccountInterface $account,
  ) {
    if ($account->hasPermission('administer all user statistics')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    if ($operation === 'view') {
      $is_owner = $account->id() === $account->id();
      return AccessResult::allowedIf($is_owner)
        ->cachePerUser()
        ->addCacheableDependency($entity);
    }

    return AccessResult::forbidden()->cachePerPermissions()->addCacheableDependency($entity);
  }

  /**
   * {@inheritDoc}
   */
  protected function checkCreateAccess(
    AccountInterface $account,
    array $context,
    $entity_bundle = NULL,
  ) {
    return AccessResult::allowedIf($account->hasPermission('administer all user statistics'))
      ->cachePerPermissions();
  }

}
