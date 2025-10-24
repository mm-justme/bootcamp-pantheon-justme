<?php

namespace Drupal\user_statistics\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the user stats entity class.
 */
#[ContentEntityType(
  id: 'user_statistics',
  label: new TranslatableMarkup('User Statistic'),
  label_collection: new TranslatableMarkup('User Statistics'),
  // This is the mapping (connection) between the internal keys of the entity
  // and the fields in the database.
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'uid' => 'uid',
  ],
  handlers: [
    // Controls who has the right to view, create, edit, and delete entities.
    'access' => EntityAccessControlHandler::class,
    // Determines how the table with the list of entities looks in the
    // admin panel. Defines columns (buildHeader()) and rows (buildRow()).
    'list_builder' => EntityListBuilder::class,
    // Manages CRUD forms for the entity.
    'form' => [
      'default' => ContentEntityForm::class,
      'add' => ContentEntityForm::class,
      'edit' => ContentEntityForm::class,
      'delete' => ContentEntityDeleteForm::class,
    ],
    // Manages CRUD forms for the entity.Creates routes such as
    // /admin/content/user-edit-stats/add, /edit, /delete, /collection.
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  // This is a set of named routes that Drupal automatically creates based on
  // handlers and entity_keys. They determine: which URLs exist for this entity,
  // and what happens when a user opens them (view/edit/delete/list/add).
  links: [
    'canonical' => '/user-statistics/{user_statistics}',
    'add-form' => '/user-statistics/add',
    'edit-form' => '/user-statistics/{user_statistics}/edit',
    'delete-form' => '/user-statistics/{user_statistics}/delete',
    'collection' => '/user-statistics',
    // We'll use /admin as a start point. Since, it better for UI so for.
  ],
  // This is the name of the permission that Drupal uses for full
  // administrative access to the entity. This is a string that must match
  // the key in the user_statistics.permissions.yml. So, 'administer all user
  // statistics' must be in the file.
  admin_permission: 'administer all user statistics',
  base_table: 'user_statistics',
)]
class UserStatistics extends ContentEntityBase {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type,): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The user who perform the action.'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['nid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Node'))
      ->setDescription(t('The node that was viewed.'))
      ->setSetting('target_type', 'node')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['action'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Action'))
      ->setDescription(t('The type of action performed.'))
      ->setSettings([
        'allowed_values' => ['view' => 'view', 'edit' => 'edit'],
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time the event was recorded.'))
      ->setRequired(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time the record was last edited.'));

    return $fields;
  }

}
