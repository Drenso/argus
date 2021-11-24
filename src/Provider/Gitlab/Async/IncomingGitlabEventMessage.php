<?php

namespace App\Provider\Gitlab\Async;

class IncomingGitlabEventMessage
{
  public function __construct(private string $eventType, private string $content)
  {
  }

  public function getEventType(): string
  {
    return $this->eventType;
  }

  public function getContent(): string
  {
    return $this->content;
  }
}
