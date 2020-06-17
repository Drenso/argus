<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabPipelineHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Pipeline Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[project][name]');

    // Get build page info
    $user    = $this->getProp($data, '[user][name]');
    $id      = $this->getProp($data, '[object_attributes][id]');
    $numJobs = count($this->getProp($data, '[builds]'));
    $action  = $this->getProp($data, '[object_attributes][status]');
    $url     = $this->getProp($data, '[project][web_url]') . '/pipelines/' . $id;
    $sha     = substr($this->getProp($data, '[object_attributes][sha]'), 0, 8);

    $skipJobs = false;
    switch ($action) {
      case 'created':
        $fill = '[%s] Pipeline #%s for #%s with %s job(s), submitted by %s, has been ' . $this->colorize('created', IrkerUtils::COLOR_GREY) . ' [ %s ]';
        break;
      case 'pending':
        $fill = '[%s] Pipeline #%s for #%s with %s job(s), submitted by %s, is now ' . $this->colorize('pending', IrkerUtils::COLOR_GREY) . ' [ %s ]';
        break;
      case 'running':
        $fill = '[%s] Pipeline #%s for #%s with %s job(s), submitted by %s, is now ' . $this->colorize('running', IrkerUtils::COLOR_PURPLE) . ' [ %s ]';
        break;
      case 'canceled':
      case 'cancelled':
        $fill = '[%s] Pipeline #%s for #%s with %s job(s), submitted by %s, has been ' . $this->colorize('cancelled', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
        break;
      case 'failed':
        $fill = '[%s] Pipeline #%s for #%s with %s job(s), submitted by %s, has ' . $this->colorize('failed', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
        break;
      case 'success':
        $fill = '[%s] Pipeline #%s for #%s with %s job(s), submitted by %s, has ' . $this->colorize('succeeded', IrkerUtils::COLOR_GREEN) . ' [ %s ]';
        break;
      case 'skipped':
        $fill     = '[%s] Pipeline #%s for #%s, submitted by %s, has been ' . $this->colorize('skipped', IrkerUtils::COLOR_GREY) . ' [ %s ]';
        $skipJobs = true;
        break;
      default:
        $fill     = '[%s] Pipeline #%s for #%s, submitted by %s, has an unknown action... [ %s ]';
        $skipJobs = true;
    }

    if ($skipJobs) {
      return [sprintf($fill,
          $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
          $id,
          $sha,
          $user,
          $this->colorize($url, IrkerUtils::COLOR_BLUE)
      )];
    } else {
      return [sprintf($fill,
          $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
          $id,
          $sha,
          $numJobs,
          $user,
          $this->colorize($url, IrkerUtils::COLOR_BLUE)
      )];
    }
  }
}
