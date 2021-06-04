<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectPipelineEvent;
use BobV\IrkerUtils\Colorize;
use App\Utils\GitShaUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectPipelineEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
        ProjectPipelineEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectPipelineEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      $shortSha = GitShaUtils::getShortSha($event->getSha());
      $skipJobs = false;

      switch ($event->getAction()) {
        case 'created':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, has been ' . Colorize::colorize('created', Colorize::COLOR_GREY) . ' [ %s ]';
          break;
        case 'pending':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, is now ' . Colorize::colorize('pending', Colorize::COLOR_GREY) . ' [ %s ]';
          break;
        case 'waiting_for_resource':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, is now ' . Colorize::colorize('waiting for resources', Colorize::COLOR_GREY) . ' [ %s ]';
          break;
        case 'running':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, is now ' . Colorize::colorize('running', Colorize::COLOR_PURPLE) . ' [ %s ]';
          break;
        case 'canceled':
        case 'cancelled':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, has been ' . Colorize::colorize('cancelled', Colorize::COLOR_LIGHT_RED) . ' [ %s ]';
          break;
        case 'failed':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, has ' . Colorize::colorize('failed', Colorize::COLOR_DARK_RED) . ' [ %s ]';
          break;
        case 'success':
          $fill = '[%s] Pipeline #%s for %s with %s job%s, submitted by %s, has ' . Colorize::colorize('succeeded', Colorize::COLOR_GREEN) . ' [ %s ]';
          break;
        case 'skipped':
          $fill     = '[%s] Pipeline #%s for %s, submitted by %s, has been ' . Colorize::colorize('skipped', Colorize::COLOR_GREY) . ' [ %s ]';
          $skipJobs = true;
          break;
        default:
          $fill     = '[%s] Pipeline #%s for %s, submitted by %s, has an unknown action... [ %s ]';
          $skipJobs = true;
      }

      if ($skipJobs) {
        $this->message(sprintf($fill,
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            $event->getIid(),
            $shortSha,
            $event->getUser(),
            Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
        ));
      } else {
        $this->message(sprintf($fill,
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            $event->getIid(),
            $shortSha,
            $event->getNumJobs(),
            $event->getNumJobs() > 1 ? 's' : '',
            $event->getUser(),
            Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
        ));
      }
    });
  }
}
