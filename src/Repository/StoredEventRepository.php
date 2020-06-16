<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StoredEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StoredEvent|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method StoredEvent|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method StoredEvent[]    findAll()
 * @method StoredEvent[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class StoredEventRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, StoredEvent::class);
  }
}
