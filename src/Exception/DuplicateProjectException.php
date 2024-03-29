<?php

namespace App\Exception;

use App\Entity\Project;
use Exception;

class DuplicateProjectException extends Exception
{
  public function __construct(private Project $project)
  {
    parent::__construct(sprintf('Project "%s" already exists', $project->getName()));
  }

  public function getProject(): Project
  {
    return $this->project;
  }
}
