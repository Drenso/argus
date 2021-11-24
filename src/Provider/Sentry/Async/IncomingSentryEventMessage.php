<?php

namespace App\Provider\Sentry\Async;

class IncomingSentryEventMessage
{
  public function __construct(private string $resource, private string $content)
  {
  }

  public function getResource(): string
  {
    return $this->resource;
  }

  public function getContent(): string
  {
    return $this->content;
  }
}
