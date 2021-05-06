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
}
