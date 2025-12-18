<?php

namespace App\Controller;

use App\Repository\DreamRepository;
use App\Repository\OrphanageRepository;
use App\Repository\AffiliateConversionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        DreamRepository $dreamRepository,
        OrphanageRepository $orphanageRepository,
        AffiliateConversionRepository $affiliateConversionRepository
    ): Response {
        // Jeśli użytkownik jest zalogowany, przekieruj na odpowiedni panel
        $user = $this->getUser();
        if ($user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('admin_dashboard');
            }
            if (in_array('ROLE_DIRECTOR', $roles)) {
                return $this->redirectToRoute('director_dream_list');
            }
        }

        // Pobierz statystyki
        $stats = [
            'dreams' => $dreamRepository->count([]),
            'orphanages' => $orphanageRepository->count([]),
            'fulfillments' => 0, // Darowizny są wyłączone, więc 0
            'affiliatePurchases' => $affiliateConversionRepository->count([]),
        ];

        // Pobierz 3 najnowsze marzenia (status pending lub verified)
        $recentDreams = $dreamRepository->createQueryBuilder('d')
            ->where('d.status IN (:statuses)')
            ->setParameter('statuses', ['pending', 'verified', 'in_progress'])
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'stats' => $stats,
            'recentDreams' => $recentDreams,
        ]);
    }
}
