<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectMergeRequestEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectMergeRequestEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{

  public static function getSubscribedEvents()
  {
    return [
        ProjectMergeRequestEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectMergeRequestEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'open':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('opened', Colorize::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
          break;
        case 'reopen':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('reopened', Colorize::COLOR_ORANGE) . ' by %s: %s [ %s ]';
          break;
        case 'update':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('updated', Colorize::COLOR_PURPLE) . ' by %s: %s [ %s ]';
          break;
        case 'close':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('closed', Colorize::COLOR_DARK_RED) . ' by %s: %s [ %s ]';
          break;
        case 'merge':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('merged', Colorize::COLOR_GREEN) . ' by %s: %s [ %s ]';
          break;
        case 'test':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('test hook', Colorize::COLOR_DARK_RED) . ' by %s: %s [ %s ]';
          break;
        case 'approved':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('approved', Colorize::COLOR_GREEN) . ' by %s: %s [ %s ]';
          break;
        case 'unapproved':
          $fill = '[%s] Merge request !%s ' . Colorize::colorize('approval revoked', Colorize::COLOR_DARK_RED) . ' by %s: %s [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on merge request !%s by %s: %s [ %s ]';
      }

      $this->message(sprintf($fill,
          Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
          $event->getIid(),
          $event->getUser(),
          $event->getTitle(),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });
  }
}
