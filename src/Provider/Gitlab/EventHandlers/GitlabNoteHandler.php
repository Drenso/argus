<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectNoteEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabNoteHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getDiscriminator(): string
  {
    return 'Note Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data = $event->getPayload();

    if (array_key_exists('commit', $data)) {
      // It is a commit note
      $action = 'commit';
      $iid    = $this->getProp($data, '[commit][message]');
      $title  = substr($this->getProp($data, '[commit][id]'), 0, 8);
    } else if (array_key_exists('merge_request', $data)) {
      // It is a merge request note
      $action = 'merge_request';
      $iid    = $this->getProp($data, '[merge_request][iid]');
      $title  = $this->getProp($data, '[merge_request][title]');
    } else if (array_key_exists('issue', $data)) {
      // It is a issue note
      $action = 'issue';
      $iid    = $this->getProp($data, '[issue][iid]');
      $title  = $this->getProp($data, '[issue][title]');
    } else if (array_key_exists('snippet', $data)) {
      // It is a snippet note
      $action = 'snippet';
      $iid    = $this->getProp($data, '[snippet][iid]');
      $title  = $this->getProp($data, '[snippet][title]');
    } else {
      // Unknown update?
      $action = 'unknown';
      $iid    = 0;
      $title  = 'Unknown';
    }

    $this->projectEvent(new ProjectNoteEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user][name]'),
        $iid,
        $this->getProp($data, '[object_attributes][url]'),
        $action,
        $title,
        $this->getProp($data, '[object_attributes][note]')
    ));
  }
}
