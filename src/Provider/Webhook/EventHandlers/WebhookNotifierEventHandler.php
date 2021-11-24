<?php

namespace App\Provider\Webhook\EventHandlers;

use App\Entity\ProjectEnvironment;
use App\Events\ProjectEnvironment\ProjectEnvironmentUpdatedEvent;
use App\Events\Usage\UsageErrorEvent;
use App\Provider\Webhook\WebhookPayload;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class WebhookNotifierEventHandler implements EventSubscriberInterface
{
  public function __construct(
      private LoggerInterface $logger,
      private HttpClientInterface $httpClient,
      private ?string $endpoint,
      private ?string $endpointSecret)
  {
  }

  public static function getSubscribedEvents()
  {
    return [
        ProjectEnvironmentUpdatedEvent::class => ['onProjectEnvironmentUpdatedEvent', -100],
        UsageErrorEvent::class                => ['onUsageErrorEvent', -100],
    ];
  }

  /**
   * @throws Throwable
   */
  public function onProjectEnvironmentUpdatedEvent(ProjectEnvironmentUpdatedEvent $projectEvent)
  {
    switch ($projectEvent->getState()) {
      case ProjectEnvironment::STATE_FAILED:
        $state = WebhookPayload::STATUS_FAILED;
        break;
      case ProjectEnvironment::STATE_RUNNING:
        $state = WebhookPayload::STATUS_RUNNING;
        break;
      case ProjectEnvironment::STATE_OK:
        $state = WebhookPayload::STATUS_OK;
        break;
      default:
        return;
    }

    $this->onEvent($projectEvent, WebhookPayload::projectPayload($state));
  }

  /**
   * @throws Throwable
   */
  public function onUsageErrorEvent(UsageErrorEvent $usageEvent)
  {
    $this->onEvent($usageEvent, WebhookPayload::usagePayload(WebhookPayload::STATUS_FAILED));
  }

  /**
   * @throws Throwable
   */
  private function onEvent($event, WebhookPayload $payload)
  {
    if (!$this->endpoint) {
      // Webhook is disabled
      return;
    }

    $eventClass = get_class($event);
    $this->logger->info(sprintf('Starting external webhook action for event "%s"', $eventClass));
    try {
      $this->httpClient->request('POST', $this->endpoint, [
          'json'    => [
              'context' => $payload->getContext(),
              'status'  => $payload->getStatus(),
          ],
          'headers' => [
              'X-AUTH-TOKEN' => $this->endpointSecret,
          ],
      ]);

      $this->logger->info(sprintf('External webhook action for event "%s" finished', $eventClass));
    } catch (Throwable $e) {
      $this->logger->error(sprintf('External webhook action for event "%s" failed', $eventClass), [
          'error' => $e->getMessage(),
      ]);
      throw $e;
    }
  }
}
