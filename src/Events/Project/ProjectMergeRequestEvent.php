<?php

namespace App\Events\Project;

class ProjectMergeRequestEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string         $projectName,
      string         $projectHost,
      string         $user,
      string         $userHandle,
      string         $iid,
      string         $url,
      string         $action,
      private string $title)
  {
    parent::__construct($projectName, $projectHost, $user, $userHandle, $iid, $url, $action);
  }

  public function getTitle(): string
  {
    return $this->title;
  }
}
