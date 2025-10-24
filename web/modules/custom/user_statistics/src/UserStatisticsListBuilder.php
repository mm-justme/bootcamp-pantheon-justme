<?php

namespace Drupal\user_statistics;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the user statistics entity type.
 */
class UserStatisticsListBuilder extends EntityListBuilder {

  /**
   * Constructs a new UserStatisticsListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The user service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    protected AccountInterface $currentUser,
    protected DateFormatterInterface $dateFormatter,
  ) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritDoc}
   */
  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type,
  ) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('current_user'),
      $container->get('date.formatter'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader():array {
    $header['id'] = $this->t('ID');
    $header['uid'] = $this->t('User');
    $header['nid'] = $this->t('Node');
    $header['action'] = $this->t('Action');
    $header['created'] = $this->t('Created');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $row['id'] = $entity->id();

    $user = $entity->get('uid')->entity;
    $row['uid'] = $user ? $user->toLink($user->getDisplayName()) : $this->t('Anonymous');

    $node = $entity->get('nid')->entity;
    $row['nid'] = $node ? $node->toLink($node->label()) : $this->t('Missing Node');

    $row['action'] = $entity->get('action')->value;
    $row['created'] = $this->dateFormatter->format($entity->get('created')->value, 'short');

    return $row + parent::buildRow($entity);
  }

}
