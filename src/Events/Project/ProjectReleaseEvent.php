<?php

namespace App\Events\Project;

class ProjectReleaseEvent extends AbstractProjectEvent implements ProjectEvent
{
  /** @var string */
  protected $name;

  public function __construct(
      string $projectName, string $user, string $iid, string $url, string $action, string $name)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);

    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }
}
