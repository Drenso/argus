<?php

namespace App\Events\Project;

abstract class AbstractProjectEvent
{
  private string $projectHost;
  private string $projectHostScheme;

  /**
   * AbstractProjectEvent constructor.
   *
   * @param string $projectName The project name
   * @param string $projectHost The project gitlab host
   * @param string $user        The user triggering the event
   * @param string $userHandle  The user handle triggering the event
   * @param string $iid         The related id
   * @param string $url         The related url
   * @param string $action      The related action
   */
  public function __construct(
      private string $projectName,
      string         $projectHost,
      private string $user,
      private string $userHandle,
      private string $iid,
      private string $url,
      private string $action)
  {
    $this->projectHost       = parse_url($projectHost, PHP_URL_HOST);
    $this->projectHostScheme = parse_url($projectHost, PHP_URL_SCHEME);

    // Prepend user handle with @
    $this->userHandle = '@' . $userHandle;
  }

  public function getProjectName(): string
  {
    return $this->projectName;
  }

  public function getProjectHost(): string
  {
    return $this->projectHost;
  }

  public function getProjectHostScheme(): string
  {
    return $this->projectHostScheme;
  }

  public function getUser(): string
  {
    return $this->user;
  }

  public function getUserHandle(): string
  {
    return $this->userHandle;
  }

  public function getIid(): string
  {
    return $this->iid;
  }

  public function getUrl(): string
  {
    return $this->url;
  }

  public function getAction(): string
  {
    return $this->action;
  }
}
