<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectReleaseEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectReleaseEventHandler extends AbstractProjectPipelineEventHandler implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
        ProjectReleaseEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectReleaseEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'create':
          $fill = '[%s] Release %s (%s) ' . Colorize::colorize('created', Colorize::COLOR_GREEN) . ': [ %s ]';
          break;
        case 'delete':
          $fill = '[%s] Release %s (%s) ' . Colorize::colorize('removed', Colorize::COLOR_DARK_RED) . ': [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on release %s (%s): [ %s ]';
      }

      $this->message(sprintf($fill,
          Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
          $event->getName(),
          Colorize::colorize($event->getIid(), Colorize::COLOR_GREY),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });
  }
}
