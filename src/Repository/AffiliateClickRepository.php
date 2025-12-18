<?php

namespace App\Repository;

use App\Entity\AffiliateClick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AffiliateClick>
 */
class AffiliateClickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffiliateClick::class);
    }

    public function countClicksForDream(int $dreamId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.dream = :dreamId')
            ->setParameter('dreamId', $dreamId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
