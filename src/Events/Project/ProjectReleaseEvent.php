<?php

namespace App\Events\Project;

class ProjectReleaseEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string         $projectName,
      string         $projectHost,
      string         $user,
      string         $userHandle,
      string         $iid,
      string         $url,
      string         $action,
      private string $name)
  {
    parent::__construct($projectName, $projectHost, $user, $userHandle, $iid, $url, $action);
  }

  public function getName(): string
  {
    return $this->name;
  }
}
