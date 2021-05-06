<?php

namespace App\EventHandlers;

use App\Entity\ProjectEnvironment;
use App\Events\Project\ProjectDeploymentEvent;
use App\Repository\ProjectEnvironmentRepository;
use App\Repository\ProjectRepository;
use App\Service\ProjectService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectEnvironmentEventHandler implements EventSubscriberInterface
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var ProjectEnvironmentRepository
   */
  private $environmentRepository;
  /**
   * @var ProjectRepository
   */
  private $projectRepository;
  /**
   * @var ProjectService
   */
  private $projectService;

  public function __construct(
      ProjectRepository $projectRepository, ProjectEnvironmentRepository $environmentRepository,
      ProjectService $projectService, EntityManagerInterface $entityManager)
  {
    $this->projectRepository     = $projectRepository;
    $this->environmentRepository = $environmentRepository;
    $this->entityManager         = $entityManager;
    $this->projectService        = $projectService;
  }

  public static function getSubscribedEvents()
  {
    return [
        ProjectDeploymentEvent::class => ['onDeploymentEvent', -1],
    ];
  }

  public function onDeploymentEvent(ProjectDeploymentEvent $event)
  {
    if (!$project = $this->projectRepository->findOneBy(['name' => $event->getProjectName()])) {
      // Silent ignore deployment events for undefined projects
      return;
    }

    // Find environment
    if (!$environment = $this->environmentRepository->findOneBy(['project' => $project, 'name' => $event->getEnvironment()])) {
      // Create environment
      $environment = new ProjectEnvironment($project, $event->getEnvironment());
    }

    // Update the environment
    $oldState = $environment->getCurrentState();
    $environment
        ->setCurrentStateFromGitlab($event->getAction())
        ->setLastEvent(new DateTimeImmutable());

    $this->entityManager->persist($environment);
    $this->entityManager->flush();

    if ($oldState !== $environment->getCurrentState()) {
      $this->projectService->environmentUpdated();
    }
  }
}
