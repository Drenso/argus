<?php

namespace App\Provider\Irker\EventHandlers;

use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OutgoingIrcMessageEventHandler extends AbstractEventHandler implements EventSubscriberInterface
{
  /**
   * @var array
   */
  private $ircChannels;
  /**
   * @var string
   */
  private $irkerServer;
  /**
   * @var int
   */
  private $irkerPort;

  public function __construct(
      EventDispatcherInterface $dispatcher, LoggerInterface $logger,
      string $irkerServer, int $irkerPort, array $ircChannels)
  {
    parent::__construct($dispatcher, $logger);

    $this->ircChannels = $ircChannels;
    $this->irkerServer = $irkerServer;
    $this->irkerPort   = $irkerPort;
  }

  public static function getSubscribedEvents()
  {
    return [
        OutgoingIrcMessageEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(OutgoingIrcMessageEvent $event)
  {
    if (!$this->irkerServer) {
      // This handler is disabled
      return;
    }

    $this->wrapHandler($event, function () use ($event) {

      if (!$event->getChannel() || !array_key_exists($event->getChannel(), $this->ircChannels)) {
        $to = $this->ircChannels['_default'];
      } else {
        $to = $this->ircChannels[$event->getChannel()];
      }

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
