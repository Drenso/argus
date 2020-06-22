<?php

namespace App\Provider\Irker\EventHandlers;

use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OutgoingIrcMessageEventHandler extends AbstractEventHandler implements EventSubscriberInterface
{
  // todo: make configurable
  private $irkerServer = 'kic-mon.snt.utwente.nl';
  private $irkerPort = 6659;

  public static function getSubscribedEvents()
  {
    return [
        OutgoingIrcMessageEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(OutgoingIrcMessageEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      // todo: Make configurable
      $to = 'irc://irc.snt.utwente.nl:6667/#drenso-gitlab';

      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      socket_connect($socket, $this->irkerServer, $this->irkerPort);
      // We cannot use JMS serializer here, as the JSON needs to be as clear as possible
      $data = json_encode([
          'to'      => $to,
          'privmsg' => $event->getMessage(),
      ]);
      socket_send($socket, $data, strlen($data), MSG_EOF);
      socket_close($socket);
    });
  }
}
