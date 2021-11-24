<?php

namespace App\Provider\Gitlab;

class GitlabGlobalTwigService
{
  public function __construct(private GitlabInstanceDetailsService $detailsService)
  {
  }

  public function hasMultipleHosts(): bool {
    return count($this->detailsService->getConfiguredHosts()) > 1;
  }
}
