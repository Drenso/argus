<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectMergeRequestEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Gitlab\Exception\MissingPropertyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabMergeRequestEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  protected function getEventType(): string
  {
    return 'Merge Request Hook';
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

    $this->projectEvent(new ProjectMergeRequestEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[object_attributes][iid]'),
        $this->getProp($data, '[object_attributes][url]'),
        $action,
        $this->getProp($data, '[object_attributes][title]')
    ));
  }
}
