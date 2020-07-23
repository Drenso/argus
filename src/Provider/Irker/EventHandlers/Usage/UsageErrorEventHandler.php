<?php

namespace App\Provider\Irker\EventHandlers\Usage;

use App\Events\Usage\UsageErrorEvent;
use App\Provider\Irker\IrkerUtils;
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

      $this->message(sprintf('[%s@%s] Error ' . IrkerUtils::colorize('triggered', IrkerUtils::COLOR_LIGHT_RED) . ': %s [ %s ]',
          IrkerUtils::colorize($explodedRelease[0], IrkerUtils::COLOR_LIGHT_RED),
          IrkerUtils::colorize($explodedRelease[1] ?: 'unknown', IrkerUtils::COLOR_DARK_RED),
          $event->getTitle(),
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });
  }
}
