<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\ProjectEnvironment;
use App\Events\ProjectEnvironment\ProjectEnvironmentUpdatedEvent;
use App\Exception\DuplicateProjectException;
use App\Exception\ProjectNotFoundException;
use App\Provider\Gitlab\GitlabApiConnector;
use App\RemoteConfiguration\RemoteConfigurationInterface;
use App\Repository\ProjectEnvironmentRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ProjectService
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var EventDispatcherInterface
   */
  private $eventDispatcher;
  /**
   * @var GitlabApiConnector
   */
  private $gitlabApiConnector;
  /**
   * @var ProjectRepository
   */
  private $projectRepository;
  /**
   * @var ProjectEnvironmentRepository
   */
  private $projectEnvironmentRepository;
  /**
   * @var PropertyAccessorInterface
   */
  private $propertyAccessor;
  /**
   * @var ServiceLocator
   */
  private $remoteConfigurationServices;

  /**
   * ProjectService constructor.
   *
   * @param ServiceLocator               $remoteConfigurationServices
   * @param ProjectRepository            $projectRepository
   * @param ProjectEnvironmentRepository $projectEnvironmentRepository
   * @param PropertyAccessorInterface    $propertyAccessor
   * @param EntityManagerInterface       $entityManager
   * @param GitlabApiConnector           $gitlabApiConnector
   * @param EventDispatcherInterface     $eventDispatcher
   */
  public function __construct(
      ServiceLocator $remoteConfigurationServices, ProjectRepository $projectRepository,
      ProjectEnvironmentRepository $projectEnvironmentRepository, PropertyAccessorInterface $propertyAccessor,
      EntityManagerInterface $entityManager, GitlabApiConnector $gitlabApiConnector,
      EventDispatcherInterface $eventDispatcher)
  {
    $this->remoteConfigurationServices  = $remoteConfigurationServices;
    $this->projectRepository            = $projectRepository;
    $this->projectEnvironmentRepository = $projectEnvironmentRepository;
    $this->propertyAccessor             = $propertyAccessor;
    $this->entityManager                = $entityManager;
    $this->gitlabApiConnector           = $gitlabApiConnector;
    $this->eventDispatcher              = $eventDispatcher;
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

        if (!$lastDeployment = $this->propertyAccessor->getValue($details, '[last_deployment]')){
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
   * @return RemoteConfigurationInterface[]
   */
  private function configurationServices(): iterable
  {
    foreach ($this->remoteConfigurationServices->getProvidedServices() as $serviceId) {
      $remoteConfigurationService = $this->remoteConfigurationServices->get($serviceId);
      if (!$remoteConfigurationService instanceof RemoteConfigurationInterface) {
        continue;
      }

      yield $remoteConfigurationService;
    }
  }
}
