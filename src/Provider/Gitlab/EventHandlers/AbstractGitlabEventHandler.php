<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Gitlab\PropertyAccessor;
use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use App\Provider\Irker\IrkerUtils;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractGitlabEventHandler
{
  /**
   * @var LoggerInterface
   */
  protected $logger;
  /**
   * @var PropertyAccessor
   */
  protected $propertyAccessor;
  /**
   * @var EventDispatcherInterface
   */
  private $eventDispatcher;
  /**
   * @var IrkerUtils|null
   */
  private $irkerUtils;

  public static function getSubscribedEvents()
  {
    return [
        IncomingGitlabEvent::class => [
            ['onEvent', 0],
        ],
    ];
  }

  public function __construct(
      LoggerInterface $logger, PropertyAccessor $propertyAccessor, EventDispatcherInterface $eventDispatcher,
      ?IrkerUtils $irkerUtils = NULL)
  {
    $this->logger           = $logger;
    $this->propertyAccessor = $propertyAccessor;
    $this->eventDispatcher  = $eventDispatcher;
    $this->irkerUtils       = $irkerUtils;
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
      $messages = $this->handleEvent($event);
      if ($messages !== NULL) {
        foreach ($messages as $message) {
          // todo: add setting to disable irker message
          $this->eventDispatcher->dispatch(new OutgoingIrcMessageEvent($message));
        }
      }
      $this->logger->info(sprintf('Event "%s" handling finished by "%s"', $event->getEventType(), get_class($this)));
      $event->markAsHandled($this->realClass(), true, NULL);
    } catch (Exception $e) {
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
   *
   * @return string[]|null
   */
  protected abstract function handleEvent(IncomingGitlabEvent $event): ?array;

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

  protected function colorize(string $message, int $number): string
  {
    // todo: Add setting to disable irker message
    if (!$this->irkerUtils) {
      return $message;
    }

    return $this->irkerUtils->colorize($message, $number);
  }

  private function realClass(): string
  {
    return get_class($this);
  }
}
