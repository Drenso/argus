<?php

namespace App\Service;

use App\Entity\Project;
use App\Exception\DuplicateProjectException;
use App\Exception\ProjectNotFoundException;
use App\Provider\Gitlab\GitlabApiConnector;
use App\RemoteConfiguration\RemoteConfigurationInterface;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Throwable;

class ProjectService
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var GitlabApiConnector
   */
  private $gitlabApiConnector;
  /**
   * @var ProjectRepository
   */
  private $projectRepository;
  /**
   * @var ServiceLocator
   */
  private $remoteConfigurationServices;

  /**
   * ProjectService constructor.
   *
   * @param ServiceLocator         $remoteConfigurationServices
   * @param ProjectRepository      $projectRepository
   * @param EntityManagerInterface $entityManager
   * @param GitlabApiConnector     $gitlabApiConnector
   */
  public function __construct(
      ServiceLocator $remoteConfigurationServices, ProjectRepository $projectRepository,
      EntityManagerInterface $entityManager, GitlabApiConnector $gitlabApiConnector)
  {
    $this->remoteConfigurationServices = $remoteConfigurationServices;
    $this->projectRepository           = $projectRepository;
    $this->entityManager               = $entityManager;
    $this->gitlabApiConnector          = $gitlabApiConnector;
  }

  /**
   * Add a new project. Directly synchronised the correct settings with the remote services.
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

      // Create the remote configuration related to the new project
      foreach ($this->remoteConfigurationServices->getProvidedServices() as $serviceId) {
        $remoteConfigurationService = $this->remoteConfigurationServices->get($serviceId);
        if (!$remoteConfigurationService instanceof RemoteConfigurationInterface) {
          continue;
        }

        $remoteConfigurationService->syncRemoteConfiguration($project);
      }

      $this->entityManager->flush();
      $this->entityManager->commit();

      return $project;
    } catch (Throwable $e) {
      $this->entityManager->rollback();

      throw $e;
    }
  }
}
