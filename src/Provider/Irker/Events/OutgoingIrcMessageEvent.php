<?php

namespace App\Provider\Irker\Events;

use App\Events\OutgoingEvent;

class OutgoingIrcMessageEvent implements OutgoingEvent
{
  private string $message;

  public function __construct(string $message, private ?string $channel, private ?string $fallbackChannel)
  {
    $this->message         = preg_replace("/[\r\n]+/", " ", $message);
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
