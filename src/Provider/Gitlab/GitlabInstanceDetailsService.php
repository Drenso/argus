<?php

namespace App\Provider\Gitlab;

use App\Entity\Project;
use RuntimeException;

class GitlabInstanceDetailsService
{
  /** @var null|array<string, GitlabConfiguration> */
  private ?array $configuration = NULL;

  public function __construct(
      private string $gitlabUrl,
      private string $gitlabToken,
      private ?int   $mrDefaultAssigneeId,
      private string $gitlabConfigurationFile)
  {
  }

  public function getProjectPath(Project $project): string
  {
    return sprintf('%s/%s', $this->getProjectBaseUrl($project), $project->getName());
  }

  public function getProjectDiffUrl(Project $project, string $source, string $target): string
  {
    return sprintf('%s/%s/-/compare/%s...%s',
        $this->getProjectBaseUrl($project), $project->getName(), $target, $source);
  }

  public function getHostBaseUrl(string $host): string
  {
    return $this->getConfigurationForHost($host)->getBaseUrl();
  }

  public function getHostToken(string $host): string
  {
    return $this->getConfigurationForHost($host)->getToken();
  }

  public function getProjectBaseUrl(Project $project): string
  {
    return $this->getConfiguration($project)->getBaseUrl();
  }

  public function getProjectHost(Project $project): string
  {
    return $this->getConfiguration($project)->getHost();
  }

  public function getDefaultMrAssigneeIdForProject(Project $project): ?int
  {
    return $this->getConfiguration($project)->getDefaultMrAssigneeId();
  }

  /** @return string[] */
  public function getConfiguredHosts(): array
  {
    $this->resolveConfiguration();

    return array_keys($this->configuration);
  }

  private function getConfiguration(Project $project): GitlabConfiguration
  {
    $this->resolveConfiguration();

    // Fall back to default configuration when the configuration file does not exist
    // Or when the gitlab host has not yet been set for the project
    if (!file_exists($this->gitlabConfigurationFile) || $project->getHost() === NULL) {
      return array_values($this->configuration)[0]
          ?? throw new RuntimeException('Default gitlab configuration missing');
    }

    if (array_key_exists($project->getHost(), $this->configuration)) {
      return $this->configuration[$project->getHost()];
    }

    throw new RuntimeException(sprintf('Could not resolve configuration for project %s on host %s',
        $project->getName(), $project->getHost()));
  }

  private function getConfigurationForHost(string $host): GitlabConfiguration
  {
    $this->resolveConfiguration();

    if (array_key_exists($host, $this->configuration)) {
      return $this->configuration[$host];
    }

    throw new RuntimeException(sprintf('Could not resolve token for host %s', $host));
  }

  private function resolveConfiguration(): void
  {
    if ($this->configuration !== NULL) {
      return;
    }

    if (!file_exists($this->gitlabConfigurationFile)) {
      $gitlabHost          = parse_url($this->gitlabUrl, PHP_URL_HOST);
      $this->configuration = [
          $gitlabHost => new GitlabConfiguration(
              $gitlabHost,
              $this->gitlabUrl,
              $this->gitlabToken,
              $this->mrDefaultAssigneeId,
          ),
      ];

      return;
    }

    // Read the configuration file
    $configurations      = json_decode(file_get_contents($this->gitlabConfigurationFile), true);
    $this->configuration = [];
    foreach ($configurations as $host => $configuration) {
      $this->configuration[$host] = new GitlabConfiguration(
          $host,
          $configuration['base_url'],
          $configuration['token'],
          $configuration['mr_default_assignee_id'],
      );
    }
  }
}
