<?php

namespace App\Events\Project;

abstract class AbstractProjectEvent
{
  /** @var string */
  protected $projectName;
  /** @var string */
  protected $user;
  /** @var string */
  protected $iid;
  /** @var string */
  protected $url;
  /** @var string */
  protected $action;

  /**
   * AbstractProjectEvent constructor.
   *
   * @param string $projectName The project name
   * @param string $user        The user triggering the event
   * @param string $iid         The related id
   * @param string $url         The related url
   * @param string $action      The related action
   */
  public function __construct(string $projectName, string $user, string $iid, string $url, string $action)
  {
    $this->projectName = $projectName;
    $this->user        = $user;
    $this->iid         = $iid;
    $this->url         = $url;
    $this->action      = $action;
  }

  /**
   * @return string
   */
  public function getProjectName(): string
  {
    return $this->projectName;
  }

  /**
   * @return string
   */
  public function getUser(): string
  {
    return $this->user;
  }

  /**
   * @return string
   */
  public function getIid(): string
  {
    return $this->iid;
  }

  /**
   * @return string
   */
  public function getUrl(): string
  {
    return $this->url;
  }

  /**
   * @return string
   */
  public function getAction(): string
  {
    return $this->action;
  }
}
