<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Gitlab\Exception\MissingPropertyException;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabWikiHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  protected function getEventType(): string
  {
    return 'Wiki Page Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[project][name]');

    // Get wiki page info
    $user = $this->getProp($data, '[user][name]');
    $iid  = $this->getProp($data, '[object_attributes][title]');
    try {
      $message = $this->getProp($data, '[object_attributes][message]');
    } catch (MissingPropertyException $e) {
      $message = false;
    }
    $url    = $this->getProp($data, '[object_attributes][url]');
    $action = $this->getProp($data, '[object_attributes][action]');

    // Limit message length
    if (strlen($message) > 100) {
      $message = substr($message, 0, 100) . '...';
    }

    if ($message === false) {
      switch ($action) {
        case 'create':
          $fill = '[%s] Wiki page %s ' . $this->colorize('created', IrkerUtils::COLOR_GREEN) . ' by %s [ %s ]';
          break;
        case 'update':
          $fill = '[%s] Wiki page %s ' . $this->colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s [ %s ]';
          break;
        case 'delete':
          $fill = '[%s] Wiki page %s ' . $this->colorize('deleted', IrkerUtils::COLOR_LIGHT_RED) . ' by %s';

          return [sprintf($fill,
              $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
              $iid,
              $user
          )];
        default:
          $fill = '[%s] Unknown action on wiki page %s by %s [ %s ]';

          return [sprintf($fill,
              $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
              $iid,
              $user,
              $this->colorize($url, IrkerUtils::COLOR_BLUE)
          )];
      }

      return [sprintf($fill,
          $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
          $iid,
          $user,
          $this->colorize($url, IrkerUtils::COLOR_BLUE)
      )];
    }

    switch ($action) {
      case 'create':
        $fill = '[%s] Wiki page %s ' . $this->colorize('created', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
        break;
      case 'update':
        $fill = '[%s] Wiki page %s ' . $this->colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s: %s [ %s ]';
        break;
      case 'delete':
        $fill = '[%s] Wiki page %s ' . $this->colorize('deleted', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: %s';

        return [sprintf($fill,
            $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
            $iid,
            $user,
            $message
        )];
      default:
        $fill = '[%s] Unknown action on wiki page %s by %s [ %s ]';

        return [sprintf($fill,
            $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
            $iid,
            $user,
            $this->colorize($url, IrkerUtils::COLOR_BLUE)
        )];
    }

    return [sprintf($fill,
        $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
        $iid,
        $user,
        $message,
        $this->colorize($url, IrkerUtils::COLOR_BLUE)
    )];
  }
}
