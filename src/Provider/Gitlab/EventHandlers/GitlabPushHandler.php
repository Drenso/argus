<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectPushEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabPushHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Push Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data    = $event->getPayload();
    $commits = $this->getProp($data, '[commits]');
    usort($commits, function ($a, $b) {
      return $this->getProp($a, '[timestamp]') <=> $this->getProp($b, '[timestamp]');
    });

    $this->projectEvent(new ProjectPushEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user_name]'),
        preg_replace('/refs\/[^\/]*\//', '', $this->getProp($data, '[ref]')),
        $this->getProp($data, '[repository][homepage]'),
        'push',
        $commits,
        $this->getProp($data, '[total_commits_count]')
    ));
  }
}
