<?php

namespace App\Controller;

use App\Entity\Dream;
use App\Entity\Orphanage;
use App\Repository\DreamRepository;
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
    public function index(Request $request, DreamRepository $dreamRepository): Response
    {
        $filters = [
            'sort'     => $request->query->get('sort', 'created_desc'),
            'category' => $request->query->get('category'),
            'region'   => $request->query->get('region'),
            'urgent'   => $request->query->get('urgent'),
            'minPrice' => $request->query->get('minPrice'),
            'maxPrice' => $request->query->get('maxPrice'),
        ];

        $page = $request->query->getInt('page', 1);
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $qb = $dreamRepository->getDreamsWithFiltersQueryBuilder($filters);
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

        $categoryList = $dreamRepository->getDistinctCategories();
        $regionList   = $dreamRepository->getDistinctRegions();

        return $this->render('dream/index.html.twig', [
            'dreams'       => $dreams,
            'currentPage'  => $page,
            'pages'        => $pages,
            'sort'         => $filters['sort'],
            'category'     => $filters['category'],
            'region'       => $filters['region'],
            'urgent'       => $filters['urgent'],
            'minPrice'     => $filters['minPrice'],
            'maxPrice'     => $filters['maxPrice'],
            'categoryList' => $categoryList,
            'regionList'   => $regionList,
            'total'        => $total,
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
