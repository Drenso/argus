<?php

declare(strict_types=1);

namespace App\EventHandlers\Incoming;

use App\Entity\StoredEvent;
use App\Events\Incoming\IncomingEvent;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use ReflectionClass;
use ReflectionException;
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
  }
}
