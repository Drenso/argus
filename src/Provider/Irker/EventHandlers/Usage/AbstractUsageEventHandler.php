<?php

namespace App\Provider\Irker\EventHandlers\Usage;

use App\Provider\Irker\EventHandlers\AbstractEventHandler;

abstract class AbstractUsageEventHandler extends AbstractEventHandler
{
  protected function message(string $message, ?string $channel = NULL)
  {
    parent::message($message, $channel ?? 'usage');
  }
}
