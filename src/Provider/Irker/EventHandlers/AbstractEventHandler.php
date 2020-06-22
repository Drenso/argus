<?php

namespace App\Provider\Irker\EventHandlers;

use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

abstract class AbstractEventHandler
{
  /**
   * @var EventDispatcherInterface
   */
  private $dispatcher;
  /**
   * @var LoggerInterface
   */
  private $logger;

  public function __construct(EventDispatcherInterface $dispatcher, LoggerInterface $logger)
  {
    $this->dispatcher = $dispatcher;
    $this->logger     = $logger;
  }

  /**
   * Create an IRC message event
   *
   * @param string $message
   */
  protected function message(string $message)
  {
    $this->dispatcher->dispatch(new OutgoingIrcMessageEvent($message));
  }

  /**
   * Wraps the handler in order to add some logging
   *
   * @param object   $event
   * @param callable $callable
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
