<?php

namespace App\Events\Project;

class ProjectMergeRequestEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var string
   */
  protected $title;

  public function __construct(string $projectName, string $user, string $iid, string $url, string $action, string $title)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);
    $this->title = $title;
  }

  /**
   * @return string
   */
  public function getTitle(): string
  {
    return $this->title;
  }
}
