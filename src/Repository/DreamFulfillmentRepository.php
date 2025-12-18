<?php

namespace App\Repository;

use App\Entity\DreamFulfillment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DreamFulfillment>
 */
class DreamFulfillmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DreamFulfillment::class);
    }
}
