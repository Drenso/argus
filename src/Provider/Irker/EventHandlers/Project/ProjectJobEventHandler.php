<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectJobEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectJobEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
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
          $fill = '[%s] Build #%s, submitted by %s, has been ' . IrkerUtils::colorize('cancelled', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
          break;
        case 'failed':
          $fill = '[%s] Build #%s, submitted by %s, has ' . IrkerUtils::colorize('failed', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
          break;
        case 'success':
          $fill = '[%s] Build #%s, submitted by %s, has ' . IrkerUtils::colorize('succeeded', IrkerUtils::COLOR_GREEN) . ' [ %s ]';
          break;
        default:
          $fill = '[%s] Build #%s, submitted by %s, has an unknown action... [ %s ]';
      }

      $this->message(sprintf($fill,
          IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
          $event->getIid(),
          $event->getUser(),
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });
  }
}
