<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectJobEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabJobEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  // todo: make configurable
  public $disabled = true;

  protected function getEventType(): string
  {
    return 'Job Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    // Build handler is disabled
    if ($this->disabled) {
      return;
    }

    $data = $event->getPayload();
    $id   = $this->getProp($data, '[build_id]');
    $url  = $this->getProp($data, '[repository][homepage]') . '/builds/' . $id;

    $this->projectEvent(new ProjectJobEvent(
        preg_replace('/\s+/', '', $this->getProp($data, '[project_name]')),
        $this->getProp($data, '[user][name]'),
        $id,
        $url,
        $this->getProp($data, '[build_status]')
    ));
  }
}
