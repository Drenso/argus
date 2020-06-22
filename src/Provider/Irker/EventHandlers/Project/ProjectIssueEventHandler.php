<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectIssueEvent;
use App\Provider\Irker\EventHandlers\AbstractEventHandler;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectIssueEventHandler extends AbstractEventHandler implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
        ProjectIssueEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectIssueEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'open':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('opened', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
          break;
        case 'reopen':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('reopened', IrkerUtils::COLOR_ORANGE) . ' by %s: %s [ %s ]';
          break;
        case 'update':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s: %s [ %s ]';
          break;
        case 'close':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('closed', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
          break;
        case 'test':
          $fill = '[%s] Issue #%s ' . IrkerUtils::colorize('test hook', IrkerUtils::COLOR_BROWN) . ' by %s: %s [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on issue #%s by %s [ %s ]';
      }

      $this->message(sprintf($fill,
          IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
          $event->getIid(),
          $event->getUser(),
          $event->getTitle(),
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });
  }
}
