<?php

namespace App\Events\Project;

class ProjectTagEvent extends AbstractProjectEvent implements ProjectEvent
{
  public function __construct(
      string          $projectName,
      string          $projectHost,
      string          $user,
      string          $userHandle,
      string          $tag,
      string          $url,
      string          $action,
      private string  $before,
      private ?string $checkoutSha)
  {
    parent::__construct($projectName, $projectHost, $user, $userHandle, $tag, $url, $action);
  }

  public function getCheckoutSha(): ?string
  {
    return $this->checkoutSha;
  }

  public function getBefore(): string
  {
    return $this->before;
  }

  public function getTag(): string
  {
    return $this->getIid();
  }
}
