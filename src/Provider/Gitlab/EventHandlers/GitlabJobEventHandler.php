<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabJobEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  // todo: make configurable
  public $disabled = true;

  protected function getEventType(): string
  {
    return 'Job Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    // Build handler is disabled
    if ($this->disabled) {
      return NULL;
    }

    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[repository][name]');
    $repo = preg_replace('/\s+/', '', $repo);

    // Get build page info
    $user   = $this->getProp($data, '[user][name]');
    $id     = $this->getProp($data, '[build_id]');
    $action = $this->getProp($data, '[build_status]');
    $url    = $this->getProp($data, '[repository][homepage]') . '/builds/' . $id;

    switch ($action) {
      case 'created':
      case 'pending':
      case 'running':
        // Ignore these messages
        return NULL;
      case 'canceled':
      case 'cancelled':
        $fill = '[%s] Build #%s, submitted by %s, has been ' . $this->colorize('cancelled', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
        break;
      case 'failed':
        $fill = '[%s] Build #%s, submitted by %s, has ' . $this->colorize('failed', IrkerUtils::COLOR_LIGHT_RED) . ' [ %s ]';
        break;
      case 'success':
        $fill = '[%s] Build #%s, submitted by %s, has ' . $this->colorize('succeeded', IrkerUtils::COLOR_GREEN) . ' [ %s ]';
        break;
      default:
        $fill = '[%s] Build #%s, submitted by %s, has an unknown action... [ %s ]';
    }

    return [sprintf($fill,
        $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
        $id,
        $user,
        $this->colorize($url, IrkerUtils::COLOR_BLUE)
    )];
  }
}
