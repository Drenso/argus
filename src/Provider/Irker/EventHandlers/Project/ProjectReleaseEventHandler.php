<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectReleaseEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectReleaseEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
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
          $fill = '[%s] Release %s (%s) ' . IrkerUtils::colorize('created', IrkerUtils::COLOR_GREEN) . ': [ %s ]';
          break;
        case 'delete':
          $fill = '[%s] Release %s (%s) ' . IrkerUtils::colorize('removed', IrkerUtils::COLOR_DARK_RED) . ': [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on release %s (%s): [ %s ]';
      }

      $this->message(sprintf($fill,
          IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
          $event->getName(),
          IrkerUtils::colorize($event->getIid(), IrkerUtils::COLOR_GREY),
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });
  }
}
