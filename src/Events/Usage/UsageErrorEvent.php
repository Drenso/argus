<?php

namespace App\Events\Usage;

class UsageErrorEvent implements UsageEvent
{
  public function __construct(
      public readonly string $release,
      public readonly string $title,
      public readonly string $url,
      public readonly string $level)
  {
  }
}
