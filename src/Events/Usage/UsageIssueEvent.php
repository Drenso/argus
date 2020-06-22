<?php

namespace App\Events\Usage;

class UsageIssueEvent implements UsageEvent
{
  /** @var string */
  private $action;
  /** @var string */
  private $user;
  /** @var string */
  private $project;
  /** @var string */
  private $iid;
  /** @var string */
  private $title;
  /** @var string */
  private $url;

  public function __construct(string $action, string $user, string $project, string $iid, string $title, string $url)
  {
    $this->action  = $action;
    $this->user    = $user;
    $this->project = $project;
    $this->iid     = $iid;
    $this->title   = $title;
    $this->url     = $url;
  }

  /**
   * @return string
   */
  public function getAction(): string
  {
    return $this->action;
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
  public function getProject(): string
  {
    return $this->project;
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
  public function getTitle(): string
  {
    return $this->title;
  }

  /**
   * @return string
   */
  public function getUrl(): string
  {
    return $this->url;
  }
}
