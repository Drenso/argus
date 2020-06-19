<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectWikiEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Gitlab\Exception\MissingPropertyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabWikiHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  protected function getEventType(): string
  {
    return 'Wiki Page Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data = $event->getPayload();

    try {
      $message = $this->getProp($data, '[object_attributes][message]');
    } catch (MissingPropertyException $e) {
      $message = null;
    }

    $this->projectEvent(new ProjectWikiEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[object_attributes][title]'),
        $this->getProp($data, '[object_attributes][url]'),
        $this->getProp($data, '[object_attributes][action]'),
        $message
    ));

  }
}
