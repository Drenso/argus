<?php

namespace App\Provider\Irker\EventHandlers;

use App\Provider\Irker\Events\OutgoingIrcMessageEvent;
use BobV\IrkerUtils\Connector;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OutgoingIrcMessageEventHandler extends AbstractEventHandler implements EventSubscriberInterface
{
  /**
   * @var array
   */
  private $ircChannels;
  /**
   * @var Connector|null
   */
  private $connector;

  public function __construct(
      EventDispatcherInterface $dispatcher, LoggerInterface $logger,
      string $irkerServer, int $irkerPort, array $ircChannels)
  {
    parent::__construct($dispatcher, $logger);

    $this->ircChannels = $ircChannels;
    if (!$irkerServer) {
      return;
    }

    $this->connector = new Connector($irkerServer, $irkerPort);
  }

  public static function getSubscribedEvents()
  {
    return [
        OutgoingIrcMessageEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(OutgoingIrcMessageEvent $event)
  {
    if (!$this->connector) {
      // This handler is disabled
      return;
    }

    $this->wrapHandler($event, function () use ($event) {
      if (!$event->getChannel()
          || !array_key_exists($event->getChannel(), $this->ircChannels)
          || empty($this->ircChannels[$event->getChannel()])) {
        if (empty($this->ircChannels['_default'])) {
          throw new RuntimeException('The default channel is required for the Irker integration to work');
        }

        $to = $this->ircChannels['_default'];
      } else {
        $to = $this->ircChannels[$event->getChannel()];
      }

      $this->connector->send($to, $event->getMessage());
    });
  }
}
