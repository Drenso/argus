<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectPipelineEvent;
use App\Provider\Irker\IrkerUtils;
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
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, has been ' . IrkerUtils::colorize('created', IrkerUtils::COLOR_GREY) . ' [ %s ]';
          break;
        case 'pending':
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, is now ' . IrkerUtils::colorize('pending', IrkerUtils::COLOR_GREY) . ' [ %s ]';
          break;
        case 'waiting_for_resource':
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, is now ' . IrkerUtils::colorize('waiting for resources', IrkerUtils::COLOR_GREY) . ' [ %s ]';
          break;
        case 'running':
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, is now ' . IrkerUtils::colorize('running', IrkerUtils::COLOR_PURPLE) . ' [ %s ]';
          break;
        case 'canceled':
        case 'cancelled':
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, has been ' . IrkerUtils::colorize('cancelled', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
          break;
        case 'failed':
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, has ' . IrkerUtils::colorize('failed', IrkerUtils::COLOR_DARK_RED) . ' [ %s ]';
          break;
        case 'success':
          $fill = '[%s] Pipeline #%s for #%s with %s job%s, submitted by %s, has ' . IrkerUtils::colorize('succeeded', IrkerUtils::COLOR_GREEN) . ' [ %s ]';
          break;
        case 'skipped':
          $fill     = '[%s] Pipeline #%s for #%s, submitted by %s, has been ' . IrkerUtils::colorize('skipped', IrkerUtils::COLOR_GREY) . ' [ %s ]';
          $skipJobs = true;
          break;
        default:
          $fill     = '[%s] Pipeline #%s for #%s, submitted by %s, has an unknown action... [ %s ]';
          $skipJobs = true;
      }

      if ($skipJobs) {
        $this->message(sprintf($fill,
            IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
            $event->getIid(),
            $shortSha,
            $event->getUser(),
            IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
        ));
      } else {
        $this->message(sprintf($fill,
            IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
            $event->getIid(),
            $shortSha,
            $event->getNumJobs(),
            $event->getNumJobs() > 1 ? 's' : '',
            $event->getUser(),
            IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
        ));
      }
    });
  }
}
