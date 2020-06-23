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

  /**
   * Removes the remote configuration for a given project
   * Not all configuration can be removed.
   *
   * @param Project $project
   */
  function deleteRemoteConfiguration(Project $project): void;
}
