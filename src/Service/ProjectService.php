<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\ProjectEnvironment;
use App\Events\ProjectEnvironment\ProjectEnvironmentUpdatedEvent;
use App\Exception\DuplicateProjectException;
use App\Exception\ProjectNotFoundException;
use App\Provider\Gitlab\Exception\GitlabRemoteCallFailedException;
use App\Provider\Gitlab\GitlabApiConnector;
use App\Provider\Gitlab\ProjectPathService;
use App\RemoteConfiguration\RemoteConfigurationInterface;
use App\Repository\ProjectEnvironmentRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use RuntimeException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ProjectService
{
  /**
   * ProjectService constructor.
   */
  public function __construct(
      private ServiceLocator               $remoteConfigurationServices,
      private ProjectRepository            $projectRepository,
      private ProjectEnvironmentRepository $projectEnvironmentRepository,
      private ProjectPathService           $projectPathService,
      private PropertyAccessorInterface    $propertyAccessor,
      private EntityManagerInterface       $entityManager,
      private GitlabApiConnector           $gitlabApiConnector,
      private EventDispatcherInterface     $eventDispatcher)
  {
  }

  /**
   * Add a new project. Directly synchronises the correct settings with the remote services.
   *
   * @param Project $source The source project used to create a new project
   *
   * @return Project The newly managed project
   *
   * @throws DuplicateProjectException
   * @throws ProjectNotFoundException
   * @throws Throwable
   */
  public function add(Project $source): Project
  {
    // Check if not duplicate
    if ($this->projectRepository->findOneBy(['name' => $source->getName()])) {
      throw new DuplicateProjectException($source);
    }

    $this->entityManager->beginTransaction();
    try {
      $project = (new Project())
          ->fromOther($source);

      // Test whether the project actually exists
      try {
        $this->gitlabApiConnector->projectApi($project, 'GET', '');
      } catch (Throwable $e) {
        throw new ProjectNotFoundException($project);
      }

      // Store the project
      $this->entityManager->persist($project);
      $this->entityManager->flush();

      // Sync the remote project configuration
      $this->sync($project);

      // Commit the transaction
      $this->entityManager->commit();

      return $project;
    } catch (Throwable $e) {
      $this->entityManager->rollback();

      throw $e;
    }
  }

  public function createMergeRequest(?Project $project)
  {
    if ($project === NULL) {
      // Build for all outdated
      foreach ($this->getOutdated() as $project) {
        $this->createMergeRequest($project['project']);
      }

      return;
    }

    // Determine if the MR might already be open
    $openMrs = $this->gitlabApiConnector
        ->projectApi($project, 'GET', 'merge_requests?state=opened&source_branch=master&target_branch=production');
    if (!empty($openMrs)) {
      // There is already an open MR
      return;
    }

    // Build MR according to format
    $assigneeId = $_ENV['MR_DEFAULT_ASSIGNEE_ID'] ?? NULL;
    $this->gitlabApiConnector->projectApi($project, 'POST', 'merge_requests', [
        'json' => [
            'source_branch' => 'master',
            'target_branch' => 'production',
            'title'         => sprintf('[%s] Production update', (new \DateTime())->format('Y-m-d')),
            'assignee_id'   => $assigneeId ? (int)$assigneeId : NULL,
        ],
    ]);
  }

  /**
   * Delete the project. Removes remote configuration if possible
   *
   * @param Project $project
   *
   * @throws Throwable
   */
  public function delete(Project $project)
  {
    $this->entityManager->beginTransaction();

    try {
      $this->entityManager->remove($project);

      foreach ($this->configurationServices() as $service) {
        $service->deleteRemoteConfiguration($project);
      }

      $this->entityManager->flush();
      $this->entityManager->commit();
    } catch (Throwable $e) {
      $this->entityManager->rollback();

      throw $e;
    }
  }

  /**
   * Call this to broadcast the new environment state
   */
  public function environmentUpdated()
  {
    // We need to recalculate the current state
    $activeStates = $this->projectEnvironmentRepository->getActiveStates();
    foreach (array_reverse(ProjectEnvironment::STATES) as $state) {
      if (in_array($state, $activeStates)) {
        $this->eventDispatcher->dispatch(new ProjectEnvironmentUpdatedEvent($state));

        break;
      }
    }
  }

  /**
   * Get the outdated projects from Gitlab
   *
   * @return array
   * @throws GitlabRemoteCallFailedException
   */
  public function getOutdated(): array
  {
    $result = [];

    // Retrieve the projects from gitlab
    $projects = $this->gitlabApiConnector
        ->projectApi(NULL, 'GET', '?simple=true&archived=false&per_page=100&order_by=last_activity_at');

    foreach ($projects as $project) {
      $projectObj = $this->projectRepository->findOneBy([
          'name' => $this->propertyAccessor->getValue($project, '[path_with_namespace]'),
      ]);
      if (!$projectObj) {
        continue;
      }

      // For each project, retrieve the production branch
      if (!$production = $this->getBranchHash($projectObj, 'production')) {
        continue;
      }

      if (!$master = $this->getBranchHash($projectObj, 'master')) {
        throw new RuntimeException(sprintf('Master branch for project %s not found!', $projectObj->getName()));
      }

      if ($production === $master) {
        continue;
      }

      $result[] = [
          'project'         => $projectObj,
          'master_sha'      => $master,
          'production_sha'  => $production,
          'gitlab_diff_url' => $this->projectPathService->getProjectDiffUrl($projectObj, 'master', 'production'),
      ];
    }

    return $result;
  }

  /**
   * Refreshes the environment information.
   *
   * @param Project $project
   *
   * @throws Throwable
   */
  public function refreshEnvironments(Project $project): void
  {
    // Retrieve environments from Gitlab
    $environments = $this->gitlabApiConnector->projectApi($project, 'GET', 'environments?states=available');

    $this->entityManager->beginTransaction();
    try {
      // Remove current environments
      $this->projectEnvironmentRepository->clearForProject($project);

      // Build new environments
      foreach ($environments as $environment) {
        $environmentId = $this->propertyAccessor->getValue($environment, '[id]');

        // Retrieve detailed deployment information
        $details = $this->gitlabApiConnector
            ->projectApi($project, 'GET', sprintf('environments/%d', $environmentId));

        if (!$lastDeployment = $this->propertyAccessor->getValue($details, '[last_deployment]')) {
          continue;
        }

        $this->entityManager->persist(
            (new ProjectEnvironment($project, $this->propertyAccessor->getValue($environment, '[name]')))
                ->setCurrentStateFromGitlab($this->propertyAccessor->getValue($lastDeployment, '[status]'))
        );
      }

      $this->entityManager->flush();

      $this->environmentUpdated();

      $this->entityManager->commit();
    } catch (Throwable $e) {
      $this->entityManager->rollback();

      throw $e;
    }
  }

  /**
   * Syncs the remote project configuration
   *
   * @param Project $project
   */
  public function sync(Project $project)
  {
    // Create the remote configuration related to the new project
    foreach ($this->configurationServices() as $service) {
      $service->syncRemoteConfiguration($project);
    }
  }

  /**
   * @return Generator<RemoteConfigurationInterface>
   */
  private function configurationServices(): Generator
  {
    foreach ($this->remoteConfigurationServices->getProvidedServices() as $serviceId) {
      $remoteConfigurationService = $this->remoteConfigurationServices->get($serviceId);
      if (!$remoteConfigurationService instanceof RemoteConfigurationInterface) {
        continue;
      }

      yield $remoteConfigurationService;
    }
  }

  private function getBranchHash(Project $project, string $branch): ?string
  {
    try {
      $data = $this->gitlabApiConnector
          ->projectApi($project, 'GET', sprintf('repository/branches/%s', $branch));

      return $this->propertyAccessor->getValue($data, '[commit][short_id]');
    } catch (Throwable $e) {
      // Branch not found
      return NULL;
    }
  }
}
