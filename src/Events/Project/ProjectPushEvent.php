<?php

namespace App\Events\Project;

class ProjectPushEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var string
   */
  private $after;
  /**
   * @var string
   */
  private $before;
  /**
   * @var string|null
   */
  private $checkoutSha;
  /**
   * @var int
   */
  private $totalCommitCount;

  /**
   * @var array
   */
  private $commits;

  public function __construct(
      string $projectName, string $user, string $branch, string $url, string $action,
      string $before, string $after, ?string $checkoutSha, array $commits, int $totalCommitCount)
  {
    parent::__construct($projectName, $user, $branch, $url, $action);
    $this->before           = $before;
    $this->after            = $after;
    $this->checkoutSha      = $checkoutSha;
    $this->commits          = $commits;
    $this->totalCommitCount = $totalCommitCount;
  }

  /**
   * @return string
   */
  public function getAfter(): string
  {
    return $this->after;
  }

  /**
   * @return string
   */
  public function getBefore(): string
  {
    return $this->before;
  }

  /**
   * @return string|null
   */
  public function getCheckoutSha(): ?string
  {
    return $this->checkoutSha;
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
