<?php

namespace App\Events\Project;

class ProjectDeploymentEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string         $projectName,
      string         $projectHost,
      string         $user,
      string         $userHandle,
      string         $iid,
      string         $url,
      string         $action,
      private string $environment,
      private string $shortSha)
  {
    parent::__construct($projectName, $projectHost, $user, $userHandle, $iid, $url, $action);
  }

  public function getEnvironment(): string
  {
    return $this->environment;
  }

  public function getShortSha(): string
  {
    return $this->shortSha;
  }
}
