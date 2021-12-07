<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\AbstractProjectEvent;
use App\Provider\Irker\EventHandlers\AbstractEventHandler;

abstract class AbstractProjectEventHandler extends AbstractEventHandler
{
  protected function message(string $message, ?string $channel = NULL, ?string $fallbackChannel = 'project')
  {
    parent::message($message, $channel, $fallbackChannel);
  }

  protected function getUserFromEvent(AbstractProjectEvent $event): string
  {
    return $_ENV['IRKER_USE_USER_HANDLE'] === 'true' ? $event->getUserHandle() : $event->getUser();
  }
}
