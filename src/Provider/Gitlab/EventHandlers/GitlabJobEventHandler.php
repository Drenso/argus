<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectJobEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabJobEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  use IgnoreBranchNameTrait;

  // todo: make configurable
  public $disabled = true;

  protected function getDiscriminator(): string
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

    if ($this->isBranchIgnored($this->getProp($data, '[ref]'))) {
      return;
    }

    $id  = $this->getProp($data, '[build_id]');
    $url = $this->getProp($data, '[repository][homepage]') . '/builds/' . $id;

    $this->projectEvent(new ProjectJobEvent(
        preg_replace('/\s+/', '', $this->getProp($data, '[project_name]')),
        $this->getProp($data, '[repository][git_http_url]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[user][username]'),
        $id,
        $url,
        $this->getProp($data, '[build_status]')
    ));
  }
}
