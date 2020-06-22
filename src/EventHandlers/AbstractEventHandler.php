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
  /**
   * @var LoggerInterface
   */
  protected $logger;
  /**
   * @var PropertyAccessor
   */
  private $propertyAccessor;
  /**
   * @var EventDispatcherInterface
   */
  private $eventDispatcher;

  public function __construct(
      LoggerInterface $logger, PropertyAccessor $propertyAccessor, EventDispatcherInterface $eventDispatcher)
  {
    $this->logger           = $logger;
    $this->propertyAccessor = $propertyAccessor;
    $this->eventDispatcher  = $eventDispatcher;
  }

  public function wrapEventHandler(IncomingEvent $event, callable $action)
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
   *
   * @return string
   */
  protected abstract function getDiscriminator(): string;

  /**
   * @param object|array                 $object
   * @param string|PropertyPathInterface $prop
   *
   * @return mixed
   */
  protected function getProp($object, string $prop)
  {
    return $this->propertyAccessor->getProperty($object, $prop);
  }

  /**
   * Dispatch a project event
   *
   * @param ProjectEvent $event
   */
  protected function projectEvent(ProjectEvent $event)
  {
    $this->eventDispatcher->dispatch($event);
  }

  /**
   * Dispatch an usage event
   *
   * @param UsageEvent $event
   */
  protected function usageEvent(UsageEvent $event)
  {
    $this->eventDispatcher->dispatch($event);
  }

  private function realClass(): string
  {
    return get_class($this);
  }
}
