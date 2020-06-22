<?php

namespace App\Events\Usage;

class UsageErrorEvent implements UsageEvent
{
  /** @var string */
  private $release;
  /** @var string */
  private $title;
  /** @var string */
  private $url;

  public function __construct(string $release, string $title, string $url)
  {
    $this->release = $release;
    $this->title   = $title;
    $this->url     = $url;
  }

  /**
   * @return string
   */
  public function getRelease(): string
  {
    return $this->release;
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
