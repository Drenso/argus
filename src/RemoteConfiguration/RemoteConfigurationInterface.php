<?php

namespace App\RemoteConfiguration;

use App\Entity\Project;

interface RemoteConfigurationInterface
{
  /**
   * Syncs the remote configuration for the given project
   *
   * @param Project $project
   */
  function syncRemoteConfiguration(Project $project): void;
}
