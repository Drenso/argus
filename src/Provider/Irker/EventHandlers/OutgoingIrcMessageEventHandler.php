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
      $to = $event->getChannel();

      if (!$to || empty($this->ircChannels[$to])) {
        $to = $event->getFallbackChannel();
      }

      if (!$to || empty($this->ircChannels[$to])) {
        $to = '_default';
      }

      if (empty($this->ircChannels[$to])) {
        throw new RuntimeException(
            sprintf('The requested channel (%s) is not available, the Irker integration will not work', $to));
      }

      $this->connector->send($this->ircChannels[$to], $event->getMessage());
    });
  }
}
