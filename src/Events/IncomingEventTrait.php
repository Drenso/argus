<?php

declare(strict_types=1);

namespace App\Events;

trait IncomingEventTrait
{
  /** @var array */
  protected $handlers = [];

  public function isHandled(): bool
  {
    foreach ($this->handlers as $handler) {
      if ($handler['success']) {
        return true;
      }
    }

    return false;
  }

  public function isFullyHandled(): bool
  {
    if (count($this->handlers) === 0) {
      return false;
    }

    foreach ($this->handlers as $handler) {
      if (!$handler['success']) {
        return false;
      }
    }

    return true;
  }

  public function markAsHandled(string $handler, bool $success, ?string $message)
  {
    $this->handlers[] = [
        'handler' => $handler,
        'success' => $success,
        'message' => $message,
    ];

    return $this;
  }
}
