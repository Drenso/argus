<?php

namespace App\Provider\Gitlab\RemoteConfiguration;

use App\Entity\Project;
use App\Provider\Gitlab\Exception\GitlabRemoteCallFailedException;
use App\Provider\Gitlab\GitlabApiConnector;
use App\RemoteConfiguration\RemoteConfigurationInterface;
use App\Utils\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GitlabRemoteConfiguration implements RemoteConfigurationInterface
{
  /**
   * @var GitlabApiConnector
   */
  private $apiConnector;
  /**
   * @var string
   */
  private $gitlabWebhookSecret;
  /**
   * @var PropertyAccessor
   */
  private $propertyAccessor;
  /**
   * @var RouterInterface
   */
  private $router;

  public function __construct(
      GitlabApiConnector $apiConnector, RouterInterface $router, PropertyAccessor $propertyAccessor,
      bool $gitlabWebhookSecretEnabled, string $gitlabWebhookSecret)
  {
    $this->apiConnector        = $apiConnector;
    $this->router              = $router;
    $this->propertyAccessor    = $propertyAccessor;
    $this->gitlabWebhookSecret = $gitlabWebhookSecretEnabled ? $gitlabWebhookSecret : NULL;
  }

  /**
   * Sync the remote gitlab configuration
   *
   * @param Project $project
   *
   * @return void
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function syncRemoteConfiguration(Project $project)
  {
    $this->syncWebhook($project);
  }

  /**
   * Sync the remote gitlab webhook with this application
   *
   * @param Project $project
   *
   * @return void
   *
   * @throws GitlabRemoteCallFailedException
   */
  private function syncWebhook(Project $project)
  {
    $webhookUrl = $this->router->generate('app_provider_gitlab_gitlab_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL);

    // Retrieve the currently installed project hooks
    $currentHooks = $this->apiConnector->projectApi($project, 'GET', 'hooks');
    $foundHook    = array_search($webhookUrl, array_column($currentHooks, 'url'));

    if (false === $foundHook) {
      // No valid hook found, so create it
      $this->createHook($project, $webhookUrl);
    } else {
      $this->updateHook($project, $webhookUrl, $currentHooks[$foundHook]);
    }
  }

  /**
   * @param Project $project
   * @param string  $url
   *
   * @return void
   *
   * @throws GitlabRemoteCallFailedException
   */
  private function createHook(Project $project, string $url)
  {
    $this->apiConnector->projectApi($project, 'POST', 'hooks', [
        'json' => [
            'url'                        => $url,
            'push_events'                => true,
            'issues_events'              => true,
            'confidential_issues_events' => true,
            'merge_requests_events'      => true,
            'tag_push_events'            => true,
            'note_events'                => true,
            'confidential_note_events'   => true,
            'job_events'                 => true,
            'pipeline_events'            => true,
            'wiki_page_events'           => true,
            'enable_ssl_verification'    => true,
            'token'                      => $this->gitlabWebhookSecret,
        ],
    ]);
  }

  /**
   * Update the project hook: only updates the url/token, so Gitlab can be used to disable certain callbacks.
   *
   * @param Project $project
   * @param string  $url
   * @param array   $currentHook
   *
   * @throws GitlabRemoteCallFailedException
   */
  private function updateHook(Project $project, string $url, array $currentHook)
  {
    $hookId = $this->propertyAccessor->getProperty($currentHook, '[id]');
    $this->apiConnector->projectApi($project, 'PUT', sprintf('hooks/%d', $hookId), [
        'json' => [
            'url'   => $url,
            'token' => $this->gitlabWebhookSecret,
        ],
    ]);
  }

}
