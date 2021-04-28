<?php

namespace App\Provider\Sentry\EventHandlers;

use App\Events\Usage\UsageErrorEvent;
use App\Exception\MissingPropertyException;
use App\Provider\Sentry\Events\IncomingSentryEvent;
use App\Utils\PropertyAccessor;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SentryEventAlertEventHandler extends AbstractSentryEventHandler implements EventSubscriberInterface
{

  /**
   * @var HttpClientInterface
   */
  private $httpClient;
  /**
   * @var string
   */
  private $sentrySecret;
  /**
   * @var SerializerInterface
   */
  private $serializer;

  public function __construct(
      LoggerInterface $logger, PropertyAccessor $propertyAccessor, EventDispatcherInterface $eventDispatcher,
      SerializerInterface $serializer, HttpClientInterface $httpClient, string $sentrySecret)
  {
    parent::__construct($logger, $propertyAccessor, $eventDispatcher);

    $this->serializer   = $serializer;
    $this->httpClient   = $httpClient;
    $this->sentrySecret = $sentrySecret;
  }

  protected function getDiscriminator(): string
  {
    return 'event_alert';
  }

  /**
   * @param IncomingSentryEvent $event
   *
   * @throws ExceptionInterface
   * @suppress PhanTypeInvalidThrowsIsInterface
   */
  protected function handleEvent(IncomingSentryEvent $event): void
  {
    $data = $this->getProp($event->getPayload(), '[data][event]');

    // Test whether this event is ignored (which is not set in the webhook data payload)
    $response  = $this->httpClient->request('GET', $this->getProp($data, '[issue_url]'), [
        'auth_bearer' => $this->sentrySecret,
    ]);
    $issueData = $this->serializer->deserialize($response->getContent(), 'array', 'json');

    if ($this->getProp($issueData, '[status]') === 'ignored') {
      // Nothing to do for ignored issues
      return;
    }

    try {
      $release = $this->getProp($data, '[release]') ?? 'unknown';
    } catch (MissingPropertyException $e) {
      $release = 'unknown';
    }

    $this->usageEvent(new UsageErrorEvent(
        $release,
        $this->getProp($data, '[title]'),
        $this->getProp($data, '[web_url]')
    ));
  }
}
