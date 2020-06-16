<?php

declare(strict_types=1);

namespace App\Events\Incoming;

use Symfony\Contracts\EventDispatcher\Event;

class IncomingGitlabEvent extends Event implements IncomingEvent
{
  use IncomingEventTrait;

  /**
   * @var string
   */
  private $eventType;

  /**
   * @var array
   */
  private $payload;

  public function __construct(string $eventType, array $payload)
  {
    $this->payload   = $payload;
    $this->eventType = $eventType;
  }

  /**
   * @return string
   */
  public function getEventType(): string
  {
    return $this->eventType;
  }

  /**
   * @return array
   */
  public function getPayload(): array
  {
    return $this->payload;
  }
}
