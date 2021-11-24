<?php

namespace App\Provider\Sentry\Events;

use App\Events\IncomingEvent;
use App\Events\IncomingEventTrait;
use Symfony\Contracts\EventDispatcher\Event;

class IncomingSentryEvent extends Event implements IncomingEvent
{
  use IncomingEventTrait;

  public function __construct(private string $resource, private array $payload)
  {
  }

  function getDiscriminator(): string
  {
    return $this->resource;
  }

  public function getResource(): string
  {
    return $this->resource;
  }

  public function getPayload(): array
  {
    return $this->payload;
  }
}
