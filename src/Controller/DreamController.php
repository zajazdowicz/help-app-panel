<?php

namespace App\Controller;

use App\Entity\Dream;
use App\Entity\Orphanage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dreams')]
class DreamController extends AbstractController
{
    #[Route('/', name: 'app_dream_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sort = $request->query->get('sort', 'created_desc');
        $category = $request->query->get('category');
        $region = $request->query->get('region');
        $urgent = $request->query->get('urgent');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $page = $request->query->getInt('page', 1);
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $repository = $entityManager->getRepository(Dream::class);
        $qb = $repository->createQueryBuilder('d')
            ->leftJoin('d.orphanage', 'o')
            ->leftJoin('d.child', 'c')
            ->andWhere('d.status = :status')
            ->setParameter('status', 'approved');

        // filters
        if ($category) {
            $qb->andWhere('d.productCategory = :category')
                ->setParameter('category', $category);
        }
        if ($region) {
            $qb->andWhere('o.region = :region')
                ->setParameter('region', $region);
        }
        if ($urgent !== null) {
            $qb->andWhere('d.isUrgent = :urgent')
                ->setParameter('urgent', (bool)$urgent);
        }
        if (is_numeric($minPrice)) {
            $qb->andWhere('d.productPrice >= :minPrice')
                ->setParameter('minPrice', (float)$minPrice);
        }
        if (is_numeric($maxPrice)) {
            $qb->andWhere('d.productPrice <= :maxPrice')
                ->setParameter('maxPrice', (float)$maxPrice);
        }

        // sorting
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

        // pagination
        $qb->setFirstResult($offset)->setMaxResults($limit);
        $dreams = $qb->getQuery()->getResult();

        // total count
        $countQb = clone $qb;
        $countQb->resetDQLPart('orderBy')
            ->resetDQLPart('groupBy')
            ->resetDQLPart('having')
            ->setFirstResult(null)
            ->setMaxResults(null);
        $countQb->select('COUNT(d.id)');
        $total = $countQb->getQuery()->getSingleScalarResult();
        $pages = ceil($total / $limit);

        // distinct categories for filter selects
        $categoryResult = $repository->createQueryBuilder('d')
            ->select('DISTINCT d.productCategory')
            ->getQuery()
            ->getResult(Query::HYDRATE_SCALAR_COLUMN);
        $categoryList = array_filter($categoryResult, function ($val) {
            return $val !== null;
        });

        // distinct regions for filter selects
        $regionResult = $entityManager->getRepository(Orphanage::class)
            ->createQueryBuilder('o')
            ->select('DISTINCT o.region')
            ->getQuery()
            ->getResult(Query::HYDRATE_SCALAR_COLUMN);
        $regionList = array_filter($regionResult, function ($val) {
            return $val !== null;
        });

        return $this->render('dream/index.html.twig', [
            'dreams' => $dreams,
            'currentPage' => $page,
            'pages' => $pages,
            'sort' => $sort,
            'category' => $category,
            'region' => $region,
            'urgent' => $urgent,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'categoryList' => $categoryList,
            'regionList' => $regionList,
        ]);
    }

    #[Route('/{id}', name: 'app_dream_show', requirements: ['id' => '\d+'])]
    public function show(Dream $dream): Response
    {
        return $this->render('dream/show.html.twig', [
            'dream' => $dream,
        ]);
    }
}
