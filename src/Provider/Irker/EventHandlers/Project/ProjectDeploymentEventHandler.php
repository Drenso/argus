<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectDeploymentEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectDeploymentEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
        ProjectDeploymentEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectDeploymentEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'running':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . IrkerUtils::colorize('running', IrkerUtils::COLOR_PURPLE) . ' by %s: [ %s ]';
          break;
        case 'success':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . IrkerUtils::colorize('succeeded', IrkerUtils::COLOR_GREEN) . ' by %s: [ %s ]';
          break;
        case 'failed':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . IrkerUtils::colorize('failed', IrkerUtils::COLOR_DARK_RED) . ' by %s: [ %s ]';
          break;
        case 'canceled':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . IrkerUtils::colorize('canceled', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on deployment on environment "%s" (%s) by %s: [ %s ]';
      }

      $this->message(sprintf($fill,
          IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
          $event->getEnvironment(),
          IrkerUtils::colorize($event->getShortSha(), IrkerUtils::COLOR_GREY),
          $event->getUser(),
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });
  }
}
