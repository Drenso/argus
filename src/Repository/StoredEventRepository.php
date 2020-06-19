<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StoredEvent;
use DateTimeInterface;
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

  /**
   * @param DateTimeInterface $dateTime
   *
   * @return array
   *
   * @noinspection PhpUnhandledExceptionInspection
   * @noinspection PhpDocMissingThrowsInspection
   */
  public function getStatsSince(DateTimeInterface $dateTime): array
  {
    $result = $this->createQueryBuilder('e')
        ->select('COUNT(e) AS all')
        ->addSelect('COALESCE(SUM(CASE WHEN e.handled = 1 AND e.fullyHandled = 1 THEN 1 ELSE 0 END), 0) AS fully_handled')
        ->addSelect('COALESCE(SUM(CASE WHEN e.handled = 0 THEN 1 ELSE 0 END), 0) AS unhandled')
        ->addSelect('COALESCE(SUM(CASE WHEN e.handled = 1 AND e.fullyHandled = 0 THEN 1 ELSE 0 END), 0) AS partially_handled')
        ->where('e.timestamp >= :timestamp')
        ->setParameter('timestamp', $dateTime)
        ->getQuery()->getSingleResult();

    foreach ($result as $key => $value) {
      $result[$key] = (int)$value;
    }

    return $result;
  }

  /**
   * Retrieve unhandled events
   *
   * @param int $limit Default 10
   *
   * @return StoredEvent[]
   */
  public function findFullyHandled(int $limit = 10): array
  {
    return $this->createQueryBuilder('e')
        ->where('e.handled = :handled')
        ->andWhere('e.fullyHandled = :handled')
        ->setParameter('handled', true)
        ->setMaxResults($limit)
        ->orderBy('e.id')
        ->getQuery()->getArrayResult();
  }

  /**
   * Retrieve unhandled events
   *
   * @param int $limit Default 10
   *
   * @return StoredEvent[]
   */
  public function findPartiallyHandled(int $limit = 10): array
  {
    return $this->createQueryBuilder('e')
        ->where('e.handled = :handled')
        ->setParameter('handled', true)
        ->andWhere('e.fullyHandled = :fully_handled')
        ->setParameter('fully_handled', false)
        ->setMaxResults($limit)
        ->orderBy('e.id')
        ->getQuery()->getArrayResult();
  }

  /**
   * Retrieve unhandled events
   *
   * @param int $limit Default 10
   *
   * @return StoredEvent[]
   */
  public function findUnhandled(int $limit = 10): array
  {
    return $this->createQueryBuilder('e')
        ->where('e.handled = :handled')
        ->setParameter('handled', false)
        ->setMaxResults($limit)
        ->orderBy('e.id')
        ->getQuery()->getArrayResult();
  }
}
