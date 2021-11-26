<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectDeploymentEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectDeploymentEventHandler extends AbstractProjectPipelineEventHandler implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
        ProjectDeploymentEvent::class => ['onEvent', 0],
    ];
  }

  protected function message(string $message, ?string $channel = 'project_deployment', ?string $fallbackChannel = 'project')
  {
    parent::message($message, $channel, $fallbackChannel);
  }

  public function onEvent(ProjectDeploymentEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      switch ($event->getAction()) {
        case 'running':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . Colorize::colorize('running', Colorize::COLOR_PURPLE) . ' by %s: [ %s ]';
          break;
        case 'success':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . Colorize::colorize('succeeded', Colorize::COLOR_GREEN) . ' by %s: [ %s ]';
          break;
        case 'failed':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . Colorize::colorize('failed', Colorize::COLOR_DARK_RED) . ' by %s: [ %s ]';
          break;
        case 'canceled':
          $fill = '[%s] Deployment on environment "%s" (%s) ' . Colorize::colorize('canceled', Colorize::COLOR_LIGHT_RED) . ' by %s: [ %s ]';
          break;
        default:
          $fill = '[%s] Unknown action on deployment on environment "%s" (%s) by %s: [ %s ]';
      }

      $this->message(sprintf($fill,
          Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
          $event->getEnvironment(),
          Colorize::colorize($event->getShortSha(), Colorize::COLOR_GREY),
          $event->getUser(),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });
  }
}
