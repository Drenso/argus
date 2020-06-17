<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabMergeRequestEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Merge Request Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[object_attributes][target][name]');

    // Get merge request info
    $user   = $this->getProp($data, '[user][name]');
    $iid    = $this->getProp($data, '[object_attributes][iid]');
    $title  = $this->getProp($data, '[object_attributes][title]');
    $url    = $this->getProp($data, '[object_attributes][url]');
    $action = $this->getProp($data, '[object_attributes][action]');

    switch ($action) {
      case 'open':
        $fill = '[%s] Merge request !%s ' . $this->colorize('opened', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
        break;
      case 'reopen':
        $fill = '[%s] Merge request !%s ' . $this->colorize('reopened', IrkerUtils::COLOR_ORANGE) . ' by %s: %s [ %s ]';
        break;
      case 'update':
        $fill = '[%s] Merge request !%s ' . $this->colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s: %s [ %s ]';
        break;
      case 'close':
        $fill = '[%s] Merge request !%s ' . $this->colorize('closed', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
        break;
      case 'merge':
        $fill = '[%s] Merge request !%s ' . $this->colorize('merged', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
        break;
      default:
        $fill = '[%s] Unknown action on merge request !%s by %s: %s [ %s ]';
    }

    return [sprintf($fill,
        $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
        $iid,
        $user,
        $title,
        $this->colorize($url, IrkerUtils::COLOR_BLUE)
    )];
  }
}
