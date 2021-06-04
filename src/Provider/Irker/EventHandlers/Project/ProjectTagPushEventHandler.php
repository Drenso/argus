<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectTagEvent;
use BobV\IrkerUtils\Colorize;
use App\Utils\GitShaUtils;
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
          $this->message(sprintf('[%s] Tag %s (%s) ' . Colorize::colorize('created', Colorize::COLOR_GREEN) . ' by %s [ %s ]',
              Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
              $event->getTag(),
              Colorize::colorize(GitShaUtils::getShortSha($event->getCheckoutSha()), Colorize::COLOR_GREY),
              $event->getUser(),
              Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
          ));
          break;
        case 'removed':
          $this->message(sprintf('[%s] Tag %s (%s) ' . Colorize::colorize('removed', Colorize::COLOR_DARK_RED) . ' by %s',
              Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
              $event->getTag(),
              Colorize::colorize(GitShaUtils::getShortSha($event->getBefore()), Colorize::COLOR_GREY),
              $event->getUser()
          ));
          break;
        default:
          $this->message(sprintf('[%s] Unknown tag event %s (%s) by %s',
              Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
              Colorize::colorize($event->getTag(), Colorize::COLOR_GREEN),
              Colorize::colorize(GitShaUtils::getShortSha($event->getBefore()), Colorize::COLOR_GREY),
              $event->getUser()
          ));
          break;
      }
    });
  }

}
