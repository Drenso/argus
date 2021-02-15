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
   * @inheritDoc
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function syncRemoteConfiguration(Project $project): void
  {
    $this->syncProjectSettings($project);
    $this->syncWebhook($project);
  }

  /**
   * @inheritDoc
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function deleteRemoteConfiguration(Project $project): void
  {
    // Test whether the project actually exists
    try {
      $this->apiConnector->projectApi($project, 'GET', '');
    } catch (GitlabRemoteCallFailedException $e) {
      if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
        // Project does not exist, ignore
        return;
      }

      throw $e;
    }

    $this->removeWebhook($project);
  }

  /**
   * Sync the default project settings that are applicable to all projects
   *
   * @param Project $project
   *
   * @throws GitlabRemoteCallFailedException
   */
  private function syncProjectSettings(Project $project)
  {
    $this->apiConnector->projectApi($project, 'PUT', '', [
        'json' => [
            'merge_method'                                     => 'ff',
            'remove_source_branch_after_merge'                 => true,
            'only_allow_merge_if_pipeline_succeeds'            => true,
            'allow_merge_on_skipped_pipeline'                  => false,
            'only_allow_merge_if_all_discussions_are_resolved' => true,
        ],
    ]);
  }

  /**
   * Sync the remote gitlab webhook with this application
   *
   * @param Project $project
   *
   * @throws GitlabRemoteCallFailedException
   */
  private function syncWebhook(Project $project)
  {
    $webhookUrl   = $this->webhookUrl();
    $existingHook = $this->getExistingHook($project, $webhookUrl);

    if ($existingHook) {
      $this->updateHook($project, $webhookUrl, $existingHook);
    } else {
      $this->createHook($project, $webhookUrl);
    }
  }

  /**
   * Remove the webhook for the project
   *
   * @param Project $project
   *
   * @throws GitlabRemoteCallFailedException
   */
  private function removeWebhook(Project $project)
  {
    $webhookUrl   = $this->webhookUrl();
    $existingHook = $this->getExistingHook($project, $webhookUrl);

    if (!$existingHook) {
      return;
    }

    $hookId = $this->propertyAccessor->getProperty($existingHook, '[id]');
    $this->apiConnector->projectApi($project, 'DELETE', sprintf('hooks/%d', $hookId));
  }

  /**
   * Create a new hook for the project
   *
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
            'deployment_events'          => true,
            'releases_events'            => true,
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

  private function webhookUrl(): string
  {
    return $this->router->generate('app_provider_gitlab_gitlab_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL);
  }

  /**
   * Retrieve the existing hook
   *
   * @param Project $project
   * @param string  $webhookUrl
   *
   * @return array|null
   * @throws GitlabRemoteCallFailedException
   */
  private function getExistingHook(Project $project, string $webhookUrl): ?array
  {
    $currentHooks = $this->apiConnector->projectApi($project, 'GET', 'hooks');
    $foundHook    = array_search($webhookUrl, array_column($currentHooks, 'url'));

    if (false !== $foundHook) {
      /** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
      return $currentHooks[$foundHook];
    }

    return NULL;
  }
}
