<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Provider\Irker\EventHandlers\AbstractEventHandler;

abstract class AbstractProjectEventHandler extends AbstractEventHandler
{
  protected function message(string $message, ?string $channel = NULL)
  {
    parent::message($message, $channel ?? 'project');
  }
}
