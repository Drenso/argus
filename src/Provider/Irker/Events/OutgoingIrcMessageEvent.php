<?php

namespace App\Provider\Irker\Events;

use App\Events\OutgoingEvent;

class OutgoingIrcMessageEvent implements OutgoingEvent
{
  /** @var string|null */
  private $channel;

  /** @var string */
  private $message;

  public function __construct(string $message, ?string $channel)
  {
    $this->message = preg_replace("/[\r\n]+/", " ", $message);;
    $this->channel = $channel;
  }

  /**
   * @return string
   */
  public function getMessage(): string
  {
    return $this->message;
  }

  /**
   * @return string|null
   */
  public function getChannel(): ?string
  {
    return $this->channel;
  }
}
