<?php

namespace App\Events\Project;

class ProjectNoteEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var string
   */
  protected $note;
  /**
   * @var string
   */
  private $title;

  public function __construct(
      string $projectName, string $user, string $iid, string $url, string $action, string $title, string $note)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);

    $this->title = $title;
    $this->note  = $note;
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
  public function getNote(): string
  {
    return $this->note;
  }
}
