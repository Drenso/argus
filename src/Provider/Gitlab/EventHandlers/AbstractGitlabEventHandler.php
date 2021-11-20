<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\EventHandlers\AbstractEventHandler;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;

abstract class AbstractGitlabEventHandler extends AbstractEventHandler
{
  public static function getSubscribedEvents()
  {
    return [
        IncomingGitlabEvent::class => [
            ['onEvent', 0],
        ],
    ];
  }

  public function onEvent(IncomingGitlabEvent $event)
  {
    $this->wrapEventHandler($event, function() use ($event){
      $this->handleEvent($event);
    });
  }

  /**
   * Handle the event
   */
  protected abstract function handleEvent(IncomingGitlabEvent $event): void;
}
