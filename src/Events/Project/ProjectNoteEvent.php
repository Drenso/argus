<?php

namespace App\Events\Project;

class ProjectNoteEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var string
   */
  protected $note;
  /**
   * @var bool
   */
  private $confidential;
  /**
   * @var string
   */
  private $title;

  public function __construct(
      string $projectName, string $user, string $iid, string $url, string $action, string $title, string $note,
      bool $confidential = false)
  {
    parent::__construct($projectName, $user, $iid, $url, $action);

    $this->title        = $title;
    $this->note         = $note;
    $this->confidential = $confidential;
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

  /**
   * @return bool
   */
  public function isConfidential(): bool
  {
    return $this->confidential;
  }
}
