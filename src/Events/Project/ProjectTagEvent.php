<?php

namespace App\Events\Project;

class ProjectTagEvent extends AbstractProjectEvent implements ProjectEvent
{
  /**
   * @var string|null
   */
  private $checkoutSha;
  /**
   * @var string
   */
  private $before;

  public function __construct(
      string $projectName, string $user, string $tag, string $url, string $action, string $before, ?string $checkoutSha)
  {
    parent::__construct($projectName, $user, $tag, $url, $action);

    $this->before      = $before;
    $this->checkoutSha = $checkoutSha;
  }

  /**
   * @return string|null
   */
  public function getCheckoutSha(): ?string
  {
    return $this->checkoutSha;
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
