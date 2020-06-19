<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Gitlab\PropertyAccessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

abstract class AbstractGitlabEventHandler
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

  public static function getSubscribedEvents()
  {
    return [
        IncomingGitlabEvent::class => [
            ['onEvent', 0],
        ],
    ];
  }

  public function __construct(
      LoggerInterface $logger, PropertyAccessor $propertyAccessor, EventDispatcherInterface $eventDispatcher)
  {
    $this->logger           = $logger;
    $this->propertyAccessor = $propertyAccessor;
    $this->eventDispatcher  = $eventDispatcher;
  }

  public function onEvent(IncomingGitlabEvent $event)
  {
    if ($event->getEventType() !== $this->getEventType()) {
      $this->logger->debug(sprintf('Skipping handler "%s" as the event type "%s" does not match with "%s"',
          get_class($this), $event->getEventType(), $this->getEventType()));

      return;
    }

    $this->logger->info(sprintf('Event "%s" handling started by "%s"', $event->getEventType(), get_class($this)));
    try {
      $this->handleEvent($event);
      $this->logger->info(sprintf('Event "%s" handling finished by "%s"', $event->getEventType(), get_class($this)));
      $event->markAsHandled($this->realClass(), true, NULL);
    } catch (Throwable $e) {
      $event->markAsHandled($this->realClass(), false, $e->getMessage());
      $this->logger->error(sprintf('Event "%s" handling failed by "%s"', $event->getEventType(), get_class($this)), [
          'error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Should return the event type (as in the X-Gitlab-Event header)
   * that will be handled by this handler
   *
   * @return string
   */
  protected abstract function getEventType(): string;

  /**
   * Handle the event
   *
   * @param IncomingGitlabEvent $event
   */
  protected abstract function handleEvent(IncomingGitlabEvent $event): void;

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

  private function realClass(): string
  {
    return get_class($this);
  }
}
