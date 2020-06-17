<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabTagPushHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Tag Push Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data = $event->getPayload();

    // Get repository info
    $repo = $this->getProp($data, '[repository][name]');
    $tag  = preg_replace('/refs\/[^\/]*\//', '', $this->getProp($data, '[ref]'));

    // Get tag data
    $name   = $this->getProp($data, '[user_name]');
    $before = substr($this->getProp($data, '[before]'), 0, 8);
    $after  = substr($this->getProp($data, '[after]'), 0, 8);

    if ($before == "00000000") {
      // Tag created
      $url = sprintf('%s/commit/%s', $this->getProp($data, '[repository][homepage]'), $after);

      return [sprintf('[%s] Tag %s (%s) created by %s [ %s ]',
          $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
          $this->colorize($tag, IrkerUtils::COLOR_GREEN),
          $this->colorize($after, IrkerUtils::COLOR_GREY),
          $name,
          $this->colorize($url, IrkerUtils::COLOR_BLUE)
      )];
    } else {
      // Tag removed
      return [sprintf('[%s] Tag %s (%s) removed by %s',
          $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
          $this->colorize($tag, IrkerUtils::COLOR_GREEN),
          $this->colorize($before, IrkerUtils::COLOR_GREY),
          $name
      )];
    }
  }
}
