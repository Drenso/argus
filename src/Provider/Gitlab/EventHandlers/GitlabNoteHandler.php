<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabNoteHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Note Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[repository][name]');

    // Get note info
    $user = $this->getProp($data, '[user][name]');
    $note = $this->getProp($data, '[object_attributes][note]');
    $url  = $this->getProp($data, '[object_attributes][url]');

    // Limit note length
    if (strlen($note) > 100) {
      $note = substr($note, 0, 100) . '...';
    }

    $iid   = "";
    $title = "";

    if (array_key_exists('commit', $data)) {
      // It is a commit note
      $fill  = '[%s] Note to commit %s ("%s") added by %s: %s [ %s ]';
      $title = substr($this->getProp($data, '[commit][id]'), 0, 8);
      $iid   = $this->getProp($data, '[commit][message]');
    } else if (array_key_exists('merge_request', $data)) {
      // It is a merge request note
      $fill  = '[%s] Note to merge request "%s" (#%s) added by %s: %s [ %s ]';
      $iid   = $this->getProp($data, '[merge_request][iid]');
      $title = $this->getProp($data, '[merge_request][title]');
    } else if (array_key_exists('issue', $data)) {
      // It is a issue note
      $fill  = '[%s] Note to issue "%s" (#%s) added by %s: %s [ %s ]';
      $iid   = $this->getProp($data, '[issue][iid]');
      $title = $this->getProp($data, '[issue][title]');
    } else if (array_key_exists('snippet', $data)) {
      // It is a snippet note, do nothing
      return NULL;
    } else {
      // Unknown update?
      return [sprintf('[%s] Unknown update by %s: %s [%s]',
          $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
          $user,
          $note,
          $this->colorize($url, IrkerUtils::COLOR_BLUE)
      )];
    }

    return [sprintf($fill,
        $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
        $title,
        $iid,
        $user,
        $note,
        $this->colorize($url, IrkerUtils::COLOR_BLUE)
    )];
  }
}
