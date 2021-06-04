<?php

namespace App\Provider\Irker\EventHandlers\Usage;

use App\Events\Usage\UsageErrorEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UsageErrorEventHandler extends AbstractUsageEventHandler implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return [
        UsageErrorEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(UsageErrorEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      $explodedRelease = explode('@', $event->getRelease());

      $this->message(sprintf('[%s@%s] Error ' . Colorize::colorize('triggered', Colorize::COLOR_LIGHT_RED) . ': %s [ %s ]',
          Colorize::colorize($explodedRelease[0], Colorize::COLOR_LIGHT_RED),
          Colorize::colorize($explodedRelease[1] ?? 'unknown', Colorize::COLOR_DARK_RED),
          $event->getTitle(),
          Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
      ));
    });
  }
}
