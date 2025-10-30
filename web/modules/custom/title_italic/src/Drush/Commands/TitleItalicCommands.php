<?php

namespace Drupal\title_italic\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\Token;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
final class TitleItalicCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Constructs a TitleItalicCommands object.
   */
  public function __construct(
    private readonly Token $token,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct();
  }

  // Description basics attributes.
  /*
  Here we say this is a new drush command. uti means update italic title.
  Short - drush uti = Full name - drush title_italic:update_title.
  #[CLI\Command(name: 'title_italic:update_title', aliases: ['uti'])]
  The description - what we see in a drush help. If we have not only one
  drush uli argument_one --option. argument_one = commandName($arg1, ...).
  It's first parameter in a commandName.
  If we have a few params, add new #[CLI\Argument(name: 'arg1'....].
  #[CLI\Argument(name: 'arg1', description: 'Argument description.')]
  Describe option which we write in a "--". It's an option in a commandName.
  drush uti --option-name=bar = $options['option-name'] === 'bar'
  in a method; The default option = without option.
  #[CLI\Option(name: 'option-name', description: 'Option description')]
  Example of the usage. drush [command-name OR alias] [arguments] [--options].
  Drush title_italic:command-name myArg --option-name=myValue.
  #[CLI\Usage(name: 'title_italic:update_title uti', description: 'Usage desc')]
  public function commandName($arg1, $options = ['option-name' => 'default']) {
  $this->logger()->success(dt('Achievement unlocked.'));}*/

  /**
   * Copy node.title into field_custom_italic for posts.
   */
  #[CLI\Command(name: 'title_italic:update_title', aliases: ['uti'])]
  #[CLI\FieldLabels(labels: [
    'nid' => 'Node ID',
    'old_title' => 'Old (node.title)',
    'new_title' => 'New (field_custom_italic)',
    'status' => 'Status',
  ])]
  #[CLI\DefaultTableFields(fields: ['nid', 'old_title', 'new_title', 'status'])]
  #[CLI\FilterDefaultField(field: 'nid')]
  public function updateTitle(
    $options = [
      'bundle' => 'posts',
      // Means new text format what we created before.
      'text-format' => 'italic_only',
      'dry-run' => FALSE,
    ],
  ): RowsOfFields {
    // Get the storage of nodes.
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Chose bundle. (look at the node_field_data, the field type in a DB).
    // We need posts, since we created there the new Custom Italic field.
    $bundle = $options['bundle'] ?? 'posts';
    $format = $options['text-format'] ?? 'italic_only';
    $dry_run = $options['dry-run'] ?? FALSE;

    // Find all nids with the needed bundle. SQL to the node_field_data.
    $nids = $node_storage->getQuery()
      ->condition('type', $bundle)
      ->accessCheck(FALSE)
      ->execute();

    $rows = [];

    if (empty($nids)) {
      return new RowsOfFields($rows);
    }

    $nodes = $node_storage->loadMultiple($nids);

    foreach ($nodes as $node) {
      $nid = $node->id();
      // Define old title. Look at node_field_data.title in a DB.
      // $node->label() = node_field_data.title - exactly what we need.
      $old_title = $node->label();

      $new_field = $node->get('field_custom_italic')->value;

      // If field_custom_italic has already filled, just skip it.
      if (trim($new_field) !== '') {
        $rows[] = [
          'nid' => $nid,
          'old_title' => $old_title,
          'new_title' => $new_field,
          'status' => 'skipped (already filled)',
        ];
        // And go to the next iteration.
        continue;
      }

      // Here we're updating our new title.
      $rows[] = [
        'nid' => $nid,
        'old_title' => $old_title,
        'new_title' => $old_title,
        'status' => $dry_run ? 'dry-run' : 'updated',
      ];

      // Dry-run means only information, without a real change.
      if ($dry_run) {
        continue;
      }
      // Change value in a DB.
      $node->set('field_custom_italic', [
        'value' => $old_title,
        // We created the new text format - italic_only. Drupal needs format
        // of the field and value. Look at node__field_custom_italic table
        // field - field_custom_italic_format.
        'format' => $format,
      ]);
      $node->save();
    }

    return new RowsOfFields($rows);
  }

}
