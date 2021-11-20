<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectReleaseEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabReleaseEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  protected function getDiscriminator(): string
  {
    return 'Release Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void{
    $data = $event->getPayload();

    $this->projectEvent(new ProjectReleaseEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[project][web_url]'),
        'unknown', // The user creating the release is not available in the webhook data
        $this->getProp($data, '[tag]'),
        $this->getProp($data, '[url]'),
        $this->getProp($data, '[action]'),
        $this->getProp($data, '[name]'),
    ));
  }
}
