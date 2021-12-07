<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectIssueEvent;
use App\Exception\MissingPropertyException;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabIssueEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  use IgnoreIssueLabelTrait;

  protected function getDiscriminator(): string
  {
    return 'Issue Hook';
  }

  protected function isConfidential(): bool
  {
    return false;
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data = $event->getPayload();

    try {
      // The action prop does not exist for test hooks
      $action = $this->getProp($data, '[object_attributes][action]');
    } catch (MissingPropertyException $e) {
      $action = 'test';
    }

    // Ignore events that has one of the excluded labels
    if ($this->isIssueIgnoredByLabel($this->getProp($data, '[labels]'))) {
      return;
    }

    $this->projectEvent(new ProjectIssueEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[project][web_url]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[user][username]'),
        $this->getProp($data, '[object_attributes][iid]'),
        $this->getProp($data, '[object_attributes][url]'),
        $action,
        $this->getProp($data, '[object_attributes][title]'),
        $this->isConfidential()
    ));
  }
}
