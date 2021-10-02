<?php

namespace App\Provider\Gitlab\EventHandlers;

trait IgnoreBranchNameTrait
{
  protected function isBranchIgnored(string $branch): bool
  {
    if ($branch === '') {
      return false;
    }

    $excludedBranchPrefixes = explode(',', $_ENV['GITLAB_EXCLUDE_BRANCH_PREFIXES']) ?? [];

    foreach ($excludedBranchPrefixes as $excludedBranchPrefix) {
      if ($excludedBranchPrefix === '') {
        continue;
      }

      if (str_starts_with($branch, $excludedBranchPrefix)) {
        return true;
      }
    }

    return false;
  }
}
