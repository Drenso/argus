<?php

namespace App\Provider\Sentry\EventHandlers;

use App\Provider\Sentry\Events\IncomingSentryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SentryUninstallationEventHandler extends AbstractSentryEventHandler implements EventSubscriberInterface
{

  protected function getDiscriminator(): string
  {
    return 'uninstallation';
  }

  protected function handleEvent(IncomingSentryEvent $event): void
  {
    // This event type is ignored
  }
}
