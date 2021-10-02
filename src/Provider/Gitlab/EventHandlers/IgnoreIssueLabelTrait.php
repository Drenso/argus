<?php

namespace App\Provider\Gitlab\EventHandlers;

trait IgnoreIssueLabelTrait
{
  public function isIssueIgnoredByLabel(array $issueLabels): bool
  {
    // Ignore events that has one of the excluded labels
    $excludedLabels = explode(',', $_ENV['GITLAB_EXCLUDE_ISSUE_LABELS']) ?? [];
    foreach ($issueLabels as $issueLabel) {
      if ($issueLabel === '') {
        continue;
      }

      if (in_array($this->getProp($issueLabel, '[title]'), $excludedLabels)) {
        return true;
      }
    }

    return false;
  }
}
