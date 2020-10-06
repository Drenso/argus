<?php

namespace App\Events\Project;

class ProjectIssueEvent extends AbstractProjectEvent implements ProjectEvent
{
  /** @var string */
  protected $title;
  /** @var bool */
  protected $confidential;

  public function __construct(
      string $projectName, string $user, string $iid, string $url, string $action, string $title,
      bool $confidential = false)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);

    $this->title        = $title;
    $this->confidential = $confidential;
  }

  /**
   * @return string
   */
  public function getTitle(): string
  {
    return $this->title;
  }

  /**
   * @return bool
   */
  public function isConfidential(): bool
  {
    return $this->confidential;
  }
}
