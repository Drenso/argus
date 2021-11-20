<?php

namespace App\Events\Project;

class ProjectNoteEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string         $projectName, string $projectHost, string $user, string $iid, string $url, string $action,
      private string $title,
      private string $note,
      private bool   $confidential = false)
  {
    parent::__construct($projectName, $projectHost, $user, $iid, $url, $action);
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getNote(): string
  {
    return $this->note;
  }

  public function isConfidential(): bool
  {
    return $this->confidential;
  }
}
