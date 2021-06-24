<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectIssueEvent;
use App\Exception\MissingPropertyException;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabIssueEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
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
    $excludedLabels = explode(',', $_ENV['GITLAB_EXCLUDE_ISSUE_LABELS']);
    $issueLabels    = $this->getProp($data, '[labels]');
    foreach ($issueLabels as $issueLabel) {
      if (in_array($this->getProp($issueLabel, '[title]'), $excludedLabels)) {
        return;
      }
    }

    $this->projectEvent(new ProjectIssueEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[object_attributes][iid]'),
        $this->getProp($data, '[object_attributes][url]'),
        $action,
        $this->getProp($data, '[object_attributes][title]'),
        $this->isConfidential()
    ));
  }
}
