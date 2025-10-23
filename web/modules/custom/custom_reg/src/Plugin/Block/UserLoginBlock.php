<?php

declare(strict_types=1);

namespace Drupal\custom_reg\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
    private Request $request,
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
      $container->get('request_stack')->getCurrentRequest(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $user_cookie = $this->request->cookies->get('custom_reg_userId');

    if (!empty($user_cookie)) {
      $build['login'] = [];
    }
    else {
      $build['login'] = [
        '#type' => 'link',
        '#title' => new TranslatableMarkup('Login'),
        '#url' => Url::fromRoute('custom_reg.login'),

        '#attributes' => [
          'class' => ['use-ajax', 'btn'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => '{"width":480}',
        ],
        '#attached' => [
          'library' => ['core/drupal.dialog.ajax'],
        ],
      ];
    }

    return $build;
  }

}
