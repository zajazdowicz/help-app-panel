<?php

namespace App\Repository;

use App\Entity\AffiliateConversion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AffiliateConversion>
 */
class AffiliateConversionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffiliateConversion::class);
    }

    public function getTotalPurchasedQuantityForDream(int $dreamId): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.quantity) as total')
            ->andWhere('c.dream = :dreamId')
            ->setParameter('dreamId', $dreamId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }
}
