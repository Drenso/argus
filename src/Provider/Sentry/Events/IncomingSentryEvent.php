<?php

namespace App\Provider\Sentry\Events;

use App\Events\IncomingEvent;
use App\Events\IncomingEventTrait;
use Symfony\Contracts\EventDispatcher\Event;

class IncomingSentryEvent extends Event implements IncomingEvent
{
  use IncomingEventTrait;

  /** @var string */
  private $resource;

  /** @var array */
  private $payload;

  public function __construct(string $resource, array $payload)
  {
    $this->resource = $resource;
    $this->payload  = $payload;
  }

  function getDiscriminator(): string
  {
    return $this->resource;
  }

  /**
   * @return string
   */
  public function getResource(): string
  {
    return $this->resource;
  }

  /**
   * @return array
   */
  public function getPayload(): array
  {
    return $this->payload;
  }
}
