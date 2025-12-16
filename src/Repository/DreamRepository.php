<?php

namespace App\Repository;

use App\Entity\Dream;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dream>
 */
class DreamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dream::class);
    }

    public function findPublicDreams()
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', Dream::STATUS_VERIFIED)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDreamsByOrphanage($orphanageId)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.orphanage = :orphanageId')
            ->setParameter('orphanageId', $orphanageId)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a QueryBuilder for dreams with applied filters.
     */
    public function getDreamsWithFiltersQueryBuilder(array $filters = []): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.orphanage', 'o')
            ->andWhere('d.status = :status')
            ->setParameter('status', 'approved');

        if (!empty($filters['category'])) {
            $qb->andWhere('d.productCategory = :category')
                ->setParameter('category', $filters['category']);
        }
        if (!empty($filters['region'])) {
            $qb->andWhere('o.region = :region')
                ->setParameter('region', $filters['region']);
        }
        if (isset($filters['urgent']) && $filters['urgent'] !== '') {
            $qb->andWhere('d.isUrgent = :urgent')
                ->setParameter('urgent', (bool)$filters['urgent']);
        }
        if (is_numeric($filters['minPrice'] ?? null)) {
            $qb->andWhere('d.productPrice >= :minPrice')
                ->setParameter('minPrice', (float)$filters['minPrice']);
        }
        if (is_numeric($filters['maxPrice'] ?? null)) {
            $qb->andWhere('d.productPrice <= :maxPrice')
                ->setParameter('maxPrice', (float)$filters['maxPrice']);
        }

        // Sorting
        $sort = $filters['sort'] ?? 'created_desc';
        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('d.productPrice', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('d.productPrice', 'DESC');
                break;
            case 'created_asc':
                $qb->orderBy('d.createdAt', 'ASC');
                break;
            default:
                $qb->orderBy('d.createdAt', 'DESC');
        }

        return $qb;
    }

    /**
     * Returns distinct product categories for filter dropdown.
     */
    public function getDistinctCategories(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.productCategory')
            ->orderBy('d.productCategory', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'productCategory');
    }

    /**
     * Returns distinct regions for filter dropdown.
     */
    public function getDistinctRegions(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT o.region')
            ->leftJoin('d.orphanage', 'o')
            ->orderBy('o.region', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'region');
    }

    //    /**
    //     * @return Dream[] Returns an array of Dream objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Dream
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
