<?php

namespace App\Events\Project;

class ProjectPipelineEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var int
   */
  private $numJobs;

  /**
   * @var string
   */
  private $sha;

  public function __construct(
      string $projectName, string $user, string $iid, string $url, string $action, string $sha, int $numJobs)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);
    $this->sha     = $sha;
    $this->numJobs = $numJobs;
  }

  /**
   * @return int
   */
  public function getNumJobs(): int
  {
    return $this->numJobs;
  }

  /**
   * @return string
   */
  public function getSha(): string
  {
    return $this->sha;
  }

}
