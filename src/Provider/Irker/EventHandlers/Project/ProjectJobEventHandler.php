<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectJobEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectJobEventHandler extends AbstractProjectPipelineEventHandler implements EventSubscriberInterface
{

  public static function getSubscribedEvents()
  {
    return [
        ProjectJobEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectJobEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'created':
        case 'pending':
        case 'running':
          // Ignore these messages
          return;
        case 'canceled':
        case 'cancelled':
          $fill = '[%s] Build #%s, submitted by %s, has been ' . Colorize::colorize('cancelled', Colorize::COLOR_LIGHT_RED) . ' [ %s ]';
          break;
        case 'failed':
          $fill = '[%s] Build #%s, submitted by %s, has ' . Colorize::colorize('failed', Colorize::COLOR_DARK_RED) . ' [ %s ]';
          break;
        case 'success':
          $fill = '[%s] Build #%s, submitted by %s, has ' . Colorize::colorize('succeeded', Colorize::COLOR_GREEN) . ' [ %s ]';
          break;
        default:
          $fill = '[%s] Build #%s, submitted by %s, has an unknown action... [ %s ]';
      }

      $this->message(sprintf($fill,
          Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
          $event->getIid(),
          $this->getUserFromEvent($event),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });
  }
}
