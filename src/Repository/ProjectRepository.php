<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Project::class);
  }

  public function findOneByNameAndHost(string $name, ?string $host): ?Project
  {
    $qb = $this->createQueryBuilder('p');

    return $qb
        ->where('p.name = :name')
        ->setParameter('name', $name)
        ->andWhere($qb->expr()->orX(
            $qb->expr()->isNull('p.host'),
            $qb->expr()->eq('p.host', ':host')
        ))
        ->setParameter('host', $host)
        ->getQuery()->getOneOrNullResult();
  }
}
