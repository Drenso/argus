<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectPushEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabPushHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  use IgnoreBranchNameTrait;

  protected function getDiscriminator(): string
  {
    return 'Push Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data    = $event->getPayload();

    $branch = preg_replace('/refs\/[^\/]*\//', '', $this->getProp($data, '[ref]'));
    if ($this->isBranchIgnored($branch)) {
      return;
    }

    $commits = $this->getProp($data, '[commits]');
    usort($commits, function ($a, $b) {
      return $this->getProp($a, '[timestamp]') <=> $this->getProp($b, '[timestamp]');
    });

    $this->projectEvent(new ProjectPushEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[project][web_url]'),
        $this->getProp($data, '[user_name]'),
        $this->getProp($data, '[user_username]'),
        $branch,
        $this->getProp($data, '[repository][homepage]'),
        'push',
        $this->getProp($data, '[before]'),
        $this->getProp($data, '[after]'),
        $this->getProp($data, '[checkout_sha]'),
        $commits,
        $this->getProp($data, '[total_commits_count]')
    ));
  }
}
