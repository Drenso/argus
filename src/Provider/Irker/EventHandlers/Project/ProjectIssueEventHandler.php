<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectIssueEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectIssueEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
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
          $fill = '[%s] Issue #%s%s ' . Colorize::colorize('opened', Colorize::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
          break;
        case 'reopen':
          $fill = '[%s] Issue #%s%s ' . Colorize::colorize('reopened', Colorize::COLOR_ORANGE) . ' by %s: %s [ %s ]';
          break;
        case 'update':
          $fill = '[%s] Issue #%s%s ' . Colorize::colorize('updated', Colorize::COLOR_PURPLE) . ' by %s: %s [ %s ]';
          break;
        case 'close':
          $fill = '[%s] Issue #%s%s ' . Colorize::colorize('closed', Colorize::COLOR_GREEN) . ' by %s: %s [ %s ]';
          break;
        case 'test':
          $fill = '[%s] Issue #%s%s ' . Colorize::colorize('test hook', Colorize::COLOR_DARK_RED) . ' by %s: %s [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on issue #%s%s by %s: %s [ %s ]';
      }

      $this->message(sprintf($fill,
          Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
          $event->getIid(),
          $event->isConfidential() ? (' ' . Colorize::colorize('(confidential)', Colorize::COLOR_ORANGE)) : '',
          $event->getUser(),
          $event->getTitle(),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });
  }
}
