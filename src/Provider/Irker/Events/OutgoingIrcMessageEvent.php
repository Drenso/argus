<?php

namespace App\Provider\Irker\Events;

use App\Events\OutgoingEvent;

class OutgoingIrcMessageEvent implements OutgoingEvent
{

  /** @var string */
  private $message;

  public function __construct(string $message)
  {
    $this->message = preg_replace("/[\r\n]+/", " ", $message);;
  }

  /**
   * @return string
   */
  public function getMessage(): string
  {
    return $this->message;
  }
}
