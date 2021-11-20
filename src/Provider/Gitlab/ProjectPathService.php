<?php

namespace App\Provider\Gitlab;

use App\Entity\Project;

class ProjectPathService
{
  public function __construct(private string $gitlabUrl)
  {
  }

  public function getProjectPath(Project $project): string
  {
    return sprintf('%s/%s', $this->getProjectHostIncludingScheme($project), $project->getName());
  }

  public function getProjectDiffUrl(Project $project, string $source, string $target): string
  {
    return sprintf('%s/%s/-/compare/%s...%s',
        $this->getProjectHostIncludingScheme($project), $project->getName(), $target, $source);
  }

  public function getProjectHostIncludingScheme(Project $project): string
  {
    if ($project->getHost()) {
      return sprintf('%s://%s', $project->getHostScheme() ?? 'https', $project->getHost());
    }

    return $this->gitlabUrl;
  }
}
