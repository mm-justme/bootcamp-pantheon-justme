<?php

namespace Drupal\user_statistics\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 *
 */
class ArticleViewSubscriber implements EventSubscriberInterface {

  public function __construct(
    // All services are described in the user_statistics.services.yml. Drupal
    // need exactly this file for our services. Create method won't work here.
    private EntityTypeManagerInterface $entityTypeManager,
    private AccountInterface $currentUser,
    private CurrentRouteMatch $routeMatch,
  ) {}

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
   *
   */
  public function onKernelRequest(RequestEvent $event) {
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
    dump($node->bundle(), "HELLO ARTICLE");

  }

}
