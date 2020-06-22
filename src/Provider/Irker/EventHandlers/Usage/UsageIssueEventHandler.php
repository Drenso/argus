<?php

namespace App\Provider\Irker\EventHandlers\Usage;

use App\Events\Usage\UsageIssueEvent;
use App\Provider\Irker\EventHandlers\AbstractEventHandler;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UsageIssueEventHandler extends AbstractEventHandler implements EventSubscriberInterface
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
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('created', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
          break;
        case 'resolved':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('resolved', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
          break;
        case 'assigned':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('assigned', IrkerUtils::COLOR_PURPLE) . ' by %s: %s [ %s ]';
          break;
        case 'ignored':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('ignored', IrkerUtils::COLOR_ORANGE) . ' by %s: %s [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on issue #%s by %s [ %s ]';
      }

      return str_replace(' by Sentry', '', sprintf($fill,
          IrkerUtils::colorize($event->getProject(), IrkerUtils::COLOR_LIGHT_RED),
          $event->getIid(),
          $event->getUser(),
          $event->getTitle(),
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });

  }
}
