<?php

namespace Drupal\user_statistics\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the user stats entity class.
 */
#[ContentEntityType(
  id: 'user_stats',
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
    'canonical' => '/admin/content/user-edit-stats/{user_stats}',
    'add-form' => '/admin/content/user-edit-stats/add',
    'edit-form' => '/admin/content/user-edit-stats/{user_stats}/edit',
    'delete-form' => '/admin/content/user-edit-stats/{user_stats}/delete',
    'collection' => '/admin/content/user-edit-stats',
    // We'll use /admin as a start point. Since, it better for UI so for.
  ],
  // This is the name of the permission that Drupal uses for full
  // administrative access to the entity. This is a string that must match
  // the key in the user_statistics.permissions.yml. So, 'administer all user
  // statistics' must be in the file.
  admin_permission: 'administer all user statistics',
  base_table: 'user_stats',
)]
class UserStats extends ContentEntityBase {}
