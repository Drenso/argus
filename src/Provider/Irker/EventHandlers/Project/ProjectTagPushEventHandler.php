<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectTagEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectTagPushEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{

  public static function getSubscribedEvents()
  {
    return [
        ProjectTagEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectTagEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'created':
          $this->message(sprintf('[%s] Tag %s (%s) created by %s [ %s ]',
              IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
              IrkerUtils::colorize($event->getTag(), IrkerUtils::COLOR_GREEN),
              IrkerUtils::colorize($event->getCheckoutSha(), IrkerUtils::COLOR_GREY),
              $event->getUser(),
              IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
          ));
          break;
        case 'removed':
          $this->message(sprintf('[%s] Tag %s (%s) removed by %s',
              IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
              IrkerUtils::colorize($event->getTag(), IrkerUtils::COLOR_GREEN),
              IrkerUtils::colorize($event->getBefore(), IrkerUtils::COLOR_GREY),
              $event->getUser()
          ));
          break;
        default:
          $this->message(sprintf('[%s] Unknown tag event %s (%s) by %s',
              IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
              IrkerUtils::colorize($event->getTag(), IrkerUtils::COLOR_GREEN),
              IrkerUtils::colorize($event->getBefore(), IrkerUtils::COLOR_GREY),
              $event->getUser()
          ));
          break;
      }
    });
  }

}
