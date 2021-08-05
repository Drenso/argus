<?php

namespace App\Provider\Irker\EventHandlers\Project;

abstract class AbstractProjectPipelineEventHandler extends AbstractProjectEventHandler
{
  protected function message(string $message, ?string $channel = 'project_pipelines', ?string $fallbackChannel = 'project')
  {
    parent::message($message, $channel, $fallbackChannel);
  }
}
