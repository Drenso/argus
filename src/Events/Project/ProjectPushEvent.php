<?php

namespace App\Events\Project;

class ProjectPushEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var int
   */
  private $totalCommitCount;

  /**
   * @var array
   */
  private $commits;

  public function __construct(
      string $projectName, string $user, string $branch, string $url, string $action, array $commits,
      int $totalCommitCount)
  {
    parent::__construct($projectName, $user, $branch, $url, $action);
    $this->commits          = $commits;
    $this->totalCommitCount = $totalCommitCount;
  }

  /**
   * @return array
   */
  public function getCommits(): array
  {
    return $this->commits;
  }

  /**
   * @return string
   */
  public function getBranch(): string
  {
    return $this->iid;
  }

  /**
   * @return int
   */
  public function getTotalCommitCount(): int
  {
    return $this->totalCommitCount;
  }

}
