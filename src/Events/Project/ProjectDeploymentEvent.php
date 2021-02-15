<?php

namespace App\Events\Project;

class ProjectDeploymentEvent extends AbstractProjectEvent implements ProjectEvent
{
  /** @var string */
  protected $environment;

  /** @var string */
  protected $shortSha;

  public function __construct(
      string $projectName, string $user, string $iid, string $url, string $action, string $environment, string $shortSha)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);

    $this->environment = $environment;
    $this->shortSha    = $shortSha;
  }

  /**
   * @return string
   */
  public function getEnvironment(): string
  {
    return $this->environment;
  }

  /**
   * @return string
   */
  public function getShortSha(): string
  {
    return $this->shortSha;
  }
}
