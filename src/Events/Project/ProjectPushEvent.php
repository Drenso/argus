<?php

namespace App\Events\Project;

class ProjectPushEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string          $projectName,
      string          $projectHost,
      string          $user,
      string          $userHandle,
      string          $branch,
      string          $url,
      string          $action,
      private string  $before,
      private string  $after,
      private ?string $checkoutSha,
      private array   $commits,
      private int     $totalCommitCount)
  {
    parent::__construct($projectName, $projectHost, $user, $userHandle, $branch, $url, $action);
  }

  public function getAfter(): string
  {
    return $this->after;
  }

  public function getBefore(): string
  {
    return $this->before;
  }

  public function getCheckoutSha(): ?string
  {
    return $this->checkoutSha;
  }

  public function getCommits(): array
  {
    return $this->commits;
  }

  public function getBranch(): string
  {
    return $this->getIid();
  }

  public function getTotalCommitCount(): int
  {
    return $this->totalCommitCount;
  }

}
