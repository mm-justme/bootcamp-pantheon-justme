<?php

namespace Drupal\user_statistics\EventSubscriber;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Create a record on the user view article event.
 */
class ArticleViewSubscriber implements EventSubscriberInterface {

  /**
   * The Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new UserRequestSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   Default object for current_route_match service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger service.
   */
  public function __construct(
    // All services are described in the user_statistics.services.yml. Drupal
    // need exactly this file for our services. Create method won't work here.
    private EntityTypeManagerInterface $entityTypeManager,
    private AccountInterface $currentUser,
    private CurrentRouteMatch $routeMatch,
    private LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $this->loggerFactory->get('user_statistics');
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    // Call the method onKernelRequest on the event.
    // There are various events in the event cycle. The parameter 0 indicates
    // the order in which they can be called. 0 is just the right moment;
    // events with higher priority will already have occurred, and we will
    // get the correct result.
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 0],
    ];
  }

  /**
   * Creates a record on the user view article event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onKernelRequest(RequestEvent $event): void {
    // Only main HTTP-requests, this is node/{nid}. Without additional such as
    // a rendering block, ajax or layout-section e.c. Helps avoid to call
    // a code multiple times after loading the page.
    if (!$event->isMainRequest()) {
      return;
    }

    // Same logic as below(filter requests), we need only view = GET method.
    if ($event->getRequest()->getMethod() !== 'GET') {
      return;
    }

    // Important to react only on canonical path. Without add, edit, delete.
    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }

    // At different points in the request cycle, there may be smth else,
    // not node yet. It means go ahead, only if we have node
    // and not null or something else.
    $node = $this->routeMatch->getParameter('node');
    if (!$node instanceof NodeInterface) {
      return;
    }
    // Acc to the task we need only articles.
    if ($node->bundle() !== 'article') {
      return;
    }

    // Get uid of the user or 'Anonymous'.
    $user_id = $this->currentUser->isAnonymous() ? 'Anonymous' : $this->currentUser->id();

    try {
      // Create a record in the user_statistics table.
      $storage = $this->entityTypeManager->getStorage('user_statistics');

      $record = $storage->create([
        'uid' => ['target_id' => $user_id],
        'nid' => ['target_id' => $node->id()],
        'action' => 'view',
      ]);

      $record->save();
    }
    catch (EntityStorageException $e) {
      // This is a logical error in storage.
      $this->logger->error('Failed to save user_statistics record: @msg', ['@msg' => $e->getMessage()]);
    }
    catch (\Throwable $e) {
      // Any else error.
      $this->logger->error('Unexpected error in ArticleViewSubscriber: @msg', ['@msg' => $e->getMessage()]);

    }

  }

}
