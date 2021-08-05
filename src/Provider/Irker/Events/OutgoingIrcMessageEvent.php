<?php

namespace App\Provider\Irker\Events;

use App\Events\OutgoingEvent;

class OutgoingIrcMessageEvent implements OutgoingEvent
{
  /** @var string|null */
  private $channel;

  /** @var string|null */
  private $fallbackChannel;

  /** @var string */
  private $message;

  public function __construct(string $message, ?string $channel, ?string $fallbackChannel)
  {
    $this->message         = preg_replace("/[\r\n]+/", " ", $message);
    $this->channel         = $channel;
    $this->fallbackChannel = $fallbackChannel;
  }

  public function getMessage(): string
  {
    return $this->message;
  }

  public function getChannel(): ?string
  {
    return $this->channel;
  }

  public function getFallbackChannel(): ?string
  {
    return $this->fallbackChannel;
  }
}
