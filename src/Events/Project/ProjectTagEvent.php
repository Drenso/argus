<?php

namespace App\Events\Project;

class ProjectTagEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var string
   */
  private $after;
  /**
   * @var string
   */
  private $before;

  public function __construct(
      string $projectName, string $user, string $tag, string $url, string $action, string $before, string $after)
  {
    parent::__construct($projectName, $user, $tag, $url, $action);

    $this->before = $before;
    $this->after  = $after;
  }

  /**
   * @return string
   */
  public function getAfter(): string
  {
    return $this->after;
  }

  /**
   * @return string
   */
  public function getBefore(): string
  {
    return $this->before;
  }

  /**
   * @return string
   */
  public function getTag(): string
  {
    return $this->iid;
  }
}
