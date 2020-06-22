<?php

declare(strict_types=1);

namespace App\EventHandlers;

use App\Entity\StoredEvent;
use App\Events\IncomingEvent;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StoreIncomingEventHandler implements EventSubscriberInterface
{
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var SerializerInterface
   */
  private $serializer;

  public static function getSubscribedEvents()
  {
    $result = [];

    foreach (get_declared_classes() as $klass) {
      try {
        if ((new ReflectionClass($klass))->implementsInterface(IncomingEvent::class)) {
          $result[$klass] = ['onIncomingEvent', -255];
        }
      } catch (ReflectionException $e) {
      }
    }

    return $result;
  }

  public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
  {
    $this->em         = $em;
    $this->serializer = $serializer;
  }

  public function onIncomingEvent(IncomingEvent $event)
  {
    // Store the event in the database
    $dbEvent = (new StoredEvent())
        ->setDirection(StoredEvent::DIR_INCOMING)
        ->setHandled($event->isHandled())
        ->setFullyHandled($event->isFullyHandled())
        ->setEventName(get_class($event))
        ->setPayload($this->serializer->serialize($event, 'json'));
    $this->em->persist($dbEvent);
    $this->em->flush();

    // Throw runtime exception if the event was not handled at all
    // This allows external providers to resubmit the webhook data if they desire
    // We cannot do this on partially handled events, as some of their data has been processed already
    if (!$dbEvent->isHandled()) {
      throw new RuntimeException(sprintf('Failed to handle incoming event "%s"', get_class($event)));
    }
  }
}
