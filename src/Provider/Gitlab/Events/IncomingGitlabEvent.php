<?php

declare(strict_types=1);

namespace App\Provider\Gitlab\Events;

use App\Events\IncomingEvent;
use App\Events\IncomingEventTrait;
use Symfony\Contracts\EventDispatcher\Event;

class IncomingGitlabEvent extends Event implements IncomingEvent
{
  use IncomingEventTrait;

  public function __construct(private string $eventType, private array $payload)
  {
  }

  function getDiscriminator(): string
  {
    return $this->eventType;
  }

  public function getEventType(): string
  {
    return $this->eventType;
  }

  public function getPayload(): array
  {
    return $this->payload;
  }
}
