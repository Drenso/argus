<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Gitlab\Exception\MissingPropertyException;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabIssueEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Issue Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[repository][name]');

    // Get issue info
    $user  = $this->getProp($data, '[user][name]');
    $iid   = $this->getProp($data, '[object_attributes][iid]');
    $title = $this->getProp($data, '[object_attributes][title]');
    $url   = $this->getProp($data, '[object_attributes][url]');

    try {
      $action = $this->getProp($data, '[object_attributes][action]');
    } catch (MissingPropertyException $e) {
      $action = 'test';
    }

    switch ($action) {
      case 'open':
        $fill = '[%s] Issue #%s ' . $this->colorize('opened', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: %s [ %s ]';
        break;
      case 'reopen':
        $fill = '[%s] Issue #%s ' . $this->colorize('reopened', IrkerUtils::COLOR_ORANGE) . ' by %s: %s [ %s ]';
        break;
      case 'update':
        $fill = '[%s] Issue #%s ' . $this->colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s: %s [ %s ]';
        break;
      case 'close':
        $fill = '[%s] Issue #%s ' . $this->colorize('closed', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
        break;
      case 'test':
        $fill = '[%s] Issue #%s ' . $this->colorize('test hook', IrkerUtils::COLOR_BROWN) . ' by %s: %s [ %s ]';
        break;
      default:
        $fill = '[%s] Unknown action on issue #%s by %s [ %s ]';
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
