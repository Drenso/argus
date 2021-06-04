<?php

namespace App\Provider\Irker\EventHandlers\Usage;

use App\Events\Usage\UsageIssueEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UsageIssueEventHandler extends AbstractUsageEventHandler implements EventSubscriberInterface
{

  public static function getSubscribedEvents()
  {
    return [
        UsageIssueEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(UsageIssueEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'created':
          $fill = '[%s] Issue #%s ' . Colorize::colorize('created', Colorize::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
          break;
        case 'resolved':
          $fill = '[%s] Issue #%s ' . Colorize::colorize('resolved', Colorize::COLOR_GREEN) . ' by %s: %s [ %s ]';
          break;
        case 'assigned':
          $fill = '[%s] Issue #%s ' . Colorize::colorize('assigned', Colorize::COLOR_PURPLE) . ' by %s: %s [ %s ]';
          break;
        case 'ignored':
          $fill = '[%s] Issue #%s ' . Colorize::colorize('ignored', Colorize::COLOR_ORANGE) . ' by %s: %s [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on issue #%s by %s [ %s ]';
      }

      return str_replace(' by Sentry', '', sprintf($fill,
          Colorize::colorize($event->getProject(), Colorize::COLOR_LIGHT_RED),
          $event->getIid(),
          $event->getUser(),
          $event->getTitle(),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });

  }
}
