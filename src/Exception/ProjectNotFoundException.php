<?php

namespace App\Exception;

use App\Entity\Project;
use Exception;

class ProjectNotFoundException extends Exception
{
  public function __construct(Project $project)
  {
    parent::__construct(sprintf('Could not find project "%s"', $project->getName()));
  }
}
