<?php

namespace App\Provider\Sentry\EventHandlers;

use App\Events\Usage\UsageIssueEvent;
use App\Provider\Sentry\Events\IncomingSentryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SentryIssueEventHandler extends AbstractSentryEventHandler implements EventSubscriberInterface
{
  protected function getDiscriminator(): string
  {
    return 'issue';
  }

  protected function handleEvent(IncomingSentryEvent $event): void
  {
    $data = $event->getPayload();

    $this->usageEvent(new UsageIssueEvent(
        $this->getProp($data, '[action]'),
        $this->getProp($data, '[actor][name]'),
        $this->getProp($data, '[data][issue][project][name]'),
        $this->getProp($data, '[data][issue][shortId]'),
        $this->getProp($data, '[data][issue][title]'),
        $this->getProp($data, '[data][issue][web_url]')
    ));
  }
}
