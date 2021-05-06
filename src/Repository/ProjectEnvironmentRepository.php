<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\ProjectEnvironment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectEnvironmentRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, ProjectEnvironment::class);
  }

  public function clearForProject(Project $project): void
  {
    $this->createQueryBuilder('pe')
        ->delete()
        ->where('pe.project = :project')
        ->setParameter('project', $project)
        ->getQuery()->execute();
  }

  /**
   * @return string[]
   */
  public function getActiveStates(): array
  {
    $uniqueStates = $this->createQueryBuilder('pe')
        ->where('pe.currentState != :unknown')
        ->setParameter('unknown', ProjectEnvironment::STATE_UNKNOWN)
        ->groupBy('pe.currentState')
        ->getQuery()->getResult();

    return array_values(array_map(function (ProjectEnvironment $environment) {
      return $environment->getCurrentState();
    }, $uniqueStates));
  }
}
