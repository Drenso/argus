<?php

namespace App\Provider\Gitlab\Async;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class IncomingGitlabEventMessageHandler implements MessageHandlerInterface
{
  public function __construct(
      private SerializerInterface      $serializer,
      private EventDispatcherInterface $eventDispatcher)
  {
  }

  public function __invoke(IncomingGitlabEventMessage $msg)
  {
    $this->eventDispatcher->dispatch(new IncomingGitlabEvent(
        $msg->getEventType(),
        $this->serializer->deserialize($msg->getContent(), 'array', 'json')
    ));
  }
}
