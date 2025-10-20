<?php

declare(strict_types=1);

namespace Drupal\custom_reg\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an user login block.
 */
#[Block(
  id: 'custom_reg_user_login',
  admin_label: new TranslatableMarkup('Custom User Login'),
  category: new TranslatableMarkup('Custom'),
)]
final class UserLoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
    private readonly Connection $connection,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build['content'] = [
      '#markup' => '<a class="use-ajax"
         data-dialog-type="modal"
         data-dialog-options=\'{"width":480}\'
         href="/login-modal">Login</a>',
      '#attached' => ['library' => ['core/drupal.dialog.ajax']],
    ];
    return $build;
  }

}
