<?php

namespace App\Events\Project;

class ProjectPipelineEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string         $projectName, string $projectHost, string $user, string $iid, string $url, string $action,
      private string $sha,
      private int    $numJobs)
  {
    parent::__construct($projectName, $projectHost, $user, $iid, $url, $action);
  }

  public function getNumJobs(): int
  {
    return $this->numJobs;
  }

  public function getSha(): string
  {
    return $this->sha;
  }
}
