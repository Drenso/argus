<?php

namespace App\Events\Project;

class ProjectWikiEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string          $projectName, string $projectHost, string $user, string $iid, string $url, string $action,
      private ?string $message)
  {
    parent::__construct($projectName, $projectHost, $user, $iid, $url, $action);
  }

  public function getMessage(): ?string
  {
    return $this->message;
  }
}
