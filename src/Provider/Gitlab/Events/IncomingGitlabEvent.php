<?php

declare(strict_types=1);

namespace App\Provider\Gitlab\Events;

use App\Events\IncomingEvent;
use App\Events\IncomingEventTrait;
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
    $this->eventType = $eventType;
    $this->payload   = $payload;
  }

  function getDiscriminator(): string
  {
    return $this->eventType;
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
