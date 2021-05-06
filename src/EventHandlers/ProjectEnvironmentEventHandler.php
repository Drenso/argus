<?php

namespace App\EventHandlers;

use App\Entity\ProjectEnvironment;
use App\Events\Project\ProjectDeploymentEvent;
use App\Events\ProjectEnvironment\ProjectEnvironmentUpdatedEvent;
use App\Repository\ProjectEnvironmentRepository;
use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
   * @var EventDispatcherInterface
   */
  private $eventDispatcher;
  /**
   * @var ProjectRepository
   */
  private $projectRepository;

  public function __construct(
      ProjectRepository $projectRepository, ProjectEnvironmentRepository $environmentRepository,
      EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
  {
    $this->projectRepository     = $projectRepository;
    $this->environmentRepository = $environmentRepository;
    $this->entityManager         = $entityManager;
    $this->eventDispatcher       = $eventDispatcher;
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
      $this->eventDispatcher->dispatch(new ProjectEnvironmentUpdatedEvent());
    }
  }
}
