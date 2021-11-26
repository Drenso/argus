<?php

namespace App\EventHandlers;

use App\Events\IncomingEvent;
use App\Events\Project\ProjectEvent;
use App\Events\Usage\UsageEvent;
use App\Utils\PropertyAccessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * Abstract event handler which can serve as base for a group of event handlers
 */
abstract class AbstractEventHandler
{
  public function __construct(
      protected LoggerInterface        $logger,
      private PropertyAccessor         $propertyAccessor,
      private EventDispatcherInterface $eventDispatcher)
  {
  }

  public function wrapEventHandler(IncomingEvent $event, callable $action): void
  {
    if ($event->getDiscriminator() !== $this->getDiscriminator()) {
      $this->logger->debug(sprintf('Skipping handler "%s" as the event type "%s" does not match with "%s"',
          get_class($this), $event->getDiscriminator(), $this->getDiscriminator()));

      return;
    }

    $this->logger->info(sprintf('Event "%s" handling started by "%s"', $event->getDiscriminator(), get_class($this)));
    try {
      $action();
      $this->logger->info(sprintf('Event "%s" handling finished by "%s"', $event->getDiscriminator(), get_class($this)));
      $event->markAsHandled($this->realClass(), true, NULL);
    } catch (Throwable $e) {
      $event->markAsHandled($this->realClass(), false, $e->getMessage());
      $this->logger->error(sprintf('Event "%s" handling failed by "%s"', $event->getDiscriminator(), get_class($this)), [
          'error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Should return the discriminator for the handler to select
   * that will be handled by this handler
   */
  protected abstract function getDiscriminator(): string;

  protected function getProp(object|array $object, string|PropertyPathInterface $prop): mixed
  {
    return $this->propertyAccessor->getProperty($object, $prop);
  }

  /**
   * Dispatch a project event
   */
  protected function projectEvent(ProjectEvent $event): void
  {
    $this->eventDispatcher->dispatch($event);
  }

  /**
   * Dispatch an usage event
   */
  protected function usageEvent(UsageEvent $event): void
  {
    $this->eventDispatcher->dispatch($event);
  }

  private function realClass(): string
  {
    return get_class($this);
  }
}
