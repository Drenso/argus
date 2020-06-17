<?php

declare(strict_types=1);

namespace App\Events;

/**
 * This interface should be implemented by every incoming event
 */
interface IncomingEvent
{

  /**
   * Whether the event has been handled successfully by at least one handler
   *
   * @return bool
   */
  function isHandled(): bool;

  /**
   * Whether the event has been handled successfully by all handlers
   *
   * @return bool
   */
  function isFullyHandled(): bool;

  /**
   * @param string      $handler The class name of the handler
   * @param bool        $success Whether the handler succeeded it's task
   * @param string|null $message An optional message
   *
   * @return self
   */
  function markAsHandled(string $handler, bool $success, ?string $message);
}
