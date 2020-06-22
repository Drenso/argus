<?php

namespace App\Provider\Sentry\EventHandlers;

use App\Provider\Sentry\Events\IncomingSentryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SentryInstallationEventHandler extends AbstractSentryEventHandler implements EventSubscriberInterface
{

  protected function getDiscriminator(): string
  {
    return 'installation';
  }

  protected function handleEvent(IncomingSentryEvent $event): void
  {
    // This event type is ignored
  }
}
