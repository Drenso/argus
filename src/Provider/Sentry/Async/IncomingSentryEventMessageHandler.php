<?php

namespace App\Provider\Sentry\Async;

use App\Provider\Sentry\Events\IncomingSentryEvent;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IncomingSentryEventMessageHandler implements MessageHandlerInterface
{
  public function __construct(
      private SerializerInterface      $serializer,
      private EventDispatcherInterface $eventDispatcher)
  {
  }

  public function __invoke(IncomingSentryEventMessage $msg)
  {
    $this->eventDispatcher->dispatch(new IncomingSentryEvent(
        $msg->getResource(),
        $this->serializer->deserialize($msg->getContent(), 'array', 'json')
    ));
  }
}
