<?php

namespace App\Controller;

use App\Entity\Dream;
use App\Entity\Orphanage;
use App\Form\DreamType;
use App\Repository\DreamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[Route('/director/new', name: 'director_dream_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_DIRECTOR')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        if (!$orphanage) {
            $this->addFlash('warning', 'Nie jesteś przypisany do żadnego domu dziecka.');
            return $this->redirectToRoute('app_home');
        }
        
        $dream = new Dream();
        $dream->setOrphanage($orphanage);
        $dream->setStatus('pending');
        
        $form = $this->createForm(DreamType::class, $dream, [
            'user' => $user,
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dream);
            $entityManager->flush();
            
            $this->addFlash('success', 'Dodano nowe marzenie.');
            return $this->redirectToRoute('app_dream_index');
        }
        
        return $this->render('dream/new.html.twig', [
            'dream' => $dream,
            'form' => $form,
        ]);
    }

    #[Route('/director/{id}/edit', name: 'director_dream_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_DIRECTOR')]
    public function edit(Request $request, Dream $dream, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        // Sprawdź, czy marzenie należy do domu dziecka dyrektora
        if ($dream->getOrphanage() !== $orphanage) {
            throw $this->createAccessDeniedException('Nie masz uprawnień do edycji tego marzenia.');
        }
        
        $form = $this->createForm(DreamType::class, $dream, [
            'user' => $user,
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Zaktualizowano marzenie.');
            return $this->redirectToRoute('app_dream_show', ['id' => $dream->getId()]);
        }
        
        return $this->render('dream/edit.html.twig', [
            'dream' => $dream,
            'form' => $form,
        ]);
    }

    #[Route('/director/list', name: 'director_dream_list')]
    #[IsGranted('ROLE_DIRECTOR')]
    public function directorList(Request $request, DreamRepository $dreamRepository): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        if (!$orphanage) {
            $this->addFlash('warning', 'Nie jesteś przypisany do żadnego domu dziecka.');
            return $this->redirectToRoute('app_home');
        }
        
        $status = $request->query->get('status');
        $queryBuilder = $dreamRepository->createQueryBuilder('d')
            ->andWhere('d.orphanage = :orphanage')
            ->setParameter('orphanage', $orphanage)
            ->orderBy('d.createdAt', 'DESC');
        
        if ($status && in_array($status, array_values(Dream::STATUS_CHOICES))) {
            $queryBuilder->andWhere('d.status = :status')
                ->setParameter('status', $status);
        }
        
        $dreams = $queryBuilder->getQuery()->getResult();
        
        return $this->render('dream/director_list.html.twig', [
            'dreams' => $dreams,
            'orphanage' => $orphanage,
            'currentStatus' => $status,
            'statusChoices' => Dream::STATUS_CHOICES,
        ]);
    }

    #[Route('/director/{id}', name: 'director_dream_delete', methods: ['POST'])]
    #[IsGranted('ROLE_DIRECTOR')]
    public function delete(Request $request, Dream $dream, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        // Sprawdź, czy marzenie należy do domu dziecka dyrektora
        if ($dream->getOrphanage() !== $orphanage) {
            throw $this->createAccessDeniedException('Nie masz uprawnień do usunięcia tego marzenia.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$dream->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dream);
            $entityManager->flush();
            $this->addFlash('success', 'Usunięto marzenie.');
        }
        
        return $this->redirectToRoute('director_dream_list');
    }
}
