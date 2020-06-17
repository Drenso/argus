<?php

namespace App\Provider\Irker\EventHandlers;

use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OutgoingIrcMessageEventHandler implements EventSubscriberInterface
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  // todo: make configurable
  private $irkerServer = 'kic-mon.snt.utwente.nl';
  private $irkerPort = 6659;

  public static function getSubscribedEvents()
  {
    return [
        OutgoingIrcMessageEvent::class => ['onEvent', 0],
    ];
  }

  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  public function onEvent(OutgoingIrcMessageEvent $event)
  {
    // todo: Make configurable
    $to = 'irc://irc.snt.utwente.nl:6667/#drenso-gitlab';

    $this->logger->debug(sprintf('Pushing IRC message %s to %s', $event->getMessage(), $to));

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($socket, $this->irkerServer, $this->irkerPort);
    // We cannot use JMS serializer here, as the JSON needs to be as clear as possible
    $data = json_encode([
        'to'      => $to,
        'privmsg' => $event->getMessage(),
    ]);
    socket_send($socket, $data, strlen($data), MSG_EOF);
    socket_close($socket);
  }
}
