<?php

namespace App\Provider\Sentry\EventHandlers;

use App\EventHandlers\AbstractEventHandler;
use App\Provider\Sentry\Events\IncomingSentryEvent;

abstract class AbstractSentryEventHandler extends AbstractEventHandler
{
  public static function getSubscribedEvents()
  {
    return [
        IncomingSentryEvent::class => [
            ['onEvent', 0],
        ],
    ];
  }

  public function onEvent(IncomingSentryEvent $event)
  {
    $this->wrapEventHandler($event, function () use ($event) {
      $this->handleEvent($event);
    });
  }

  /**
   * Handle the event
   *
   * @param IncomingSentryEvent $event
   */
  protected abstract function handleEvent(IncomingSentryEvent $event): void;
}
