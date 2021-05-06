<?php

namespace App\Events\ProjectEnvironment;

class ProjectEnvironmentUpdatedEvent implements ProjectEnvironmentEvent
{
  /** @var string */
  private $state;

  public function __construct(string $state)
  {
    $this->state = $state;
  }

  public function getState(): string
  {
    return $this->state;
  }
}
