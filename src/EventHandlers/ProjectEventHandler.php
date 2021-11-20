<?php

namespace App\EventHandlers;

use App\Entity\Project;
use App\Events\Project\ProjectEvent;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Drenso\Shared\Helper\DateTimeProvider;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectEventHandler implements EventSubscriberInterface
{
  public function __construct(
      private ProjectRepository      $projectRepository,
      private EntityManagerInterface $entityManager,
      private DateTimeProvider       $dateTimeProvider)
  {
  }

  public static function getSubscribedEvents()
  {
    $result = [];

    foreach (get_declared_classes() as $klass) {
      try {
        if ((new ReflectionClass($klass))->implementsInterface(ProjectEvent::class)) {
          $result[$klass] = ['onEvent', 255];
        }
      } catch (ReflectionException $e) {
      }
    }

    return $result;
  }

  public function onEvent(ProjectEvent $event)
  {
    if (!$project = $this->projectRepository->findOneBy(['name' => $event->getProjectName()])) {
      $project = (new Project())
          ->setName($event->getProjectName());
    }

    // Update last event
    $project->setLastEvent($this->dateTimeProvider->utcNow());

    $this->entityManager->persist($project);
    $this->entityManager->flush();
  }
}
