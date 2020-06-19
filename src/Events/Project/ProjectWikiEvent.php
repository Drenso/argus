<?php

namespace App\Events\Project;

class ProjectWikiEvent extends AbstractProjectEvent implements ProjectEvent
{

  /** @var string|null */
  private $message;

  public function __construct(string $projectName, string $user, string $iid, string $url, string $action, ?string $message)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);

    $this->message = $message;
  }

  /**
   * @return string|null
   */
  public function getMessage(): ?string
  {
    return $this->message;
  }

}
