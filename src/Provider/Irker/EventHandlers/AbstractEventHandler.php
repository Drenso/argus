<?php

namespace App\Provider\Irker\EventHandlers;

use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

abstract class AbstractEventHandler
{
  public function __construct(private EventDispatcherInterface $dispatcher, private LoggerInterface $logger)
  {
  }

  /**
   * Create an IRC message event
   */
  protected function message(string $message, ?string $channel = NULL, ?string $fallbackChannel = NULL)
  {
    $this->dispatcher->dispatch(new OutgoingIrcMessageEvent($message, $channel, $fallbackChannel));
  }

  /**
   * Wraps the handler in order to add some logging
   *
   * @throws Throwable
   */
  protected function wrapHandler(object $event, callable $callable)
  {
    $this->logger->info(sprintf('Event "%s" handling started by "%s"', get_class($event), get_class($this)));
    try {
      $callable();
      $this->logger->info(sprintf('Event "%s" handling finished by "%s"', get_class($event), get_class($this)));
    } catch (Throwable $e) {
      $this->logger->error(sprintf('Event "%s" handling failed by "%s"', get_class($event), get_class($this)), [
          'error' => $e->getMessage(),
      ]);
      throw $e;
    }
  }

}
