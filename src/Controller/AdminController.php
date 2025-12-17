<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Orphanage;
use App\Entity\Dream;
use App\Entity\DreamFulfillment;
use App\Repository\UserRepository;
use App\Repository\OrphanageRepository;
use App\Repository\DreamRepository;
use App\Repository\DreamFulfillmentRepository;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        OrphanageRepository $orphanageRepository,
        DreamRepository $dreamRepository,
        DreamFulfillmentRepository $fulfillmentRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $stats = [
            'users' => $userRepository->count([]),
            'orphanages' => $orphanageRepository->count([]),
            'orphanages_pending' => $orphanageRepository->count(['isVerified' => false]),
            'dreams' => $dreamRepository->count([]),
            'dreams_pending' => $dreamRepository->count(['status' => 'pending']),
            'fulfillments' => $fulfillmentRepository->count([]),
            'categories' => $categoryRepository->count([]),
        ];
        
        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}/toggle-role', name: 'admin_user_toggle_role', methods: ['POST'])]
    public function toggleUserRole(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $role = $request->request->get('role');
        $validRoles = ['ROLE_USER', 'ROLE_DIRECTOR', 'ROLE_ADMIN'];
        
        if (!in_array($role, $validRoles)) {
            $this->addFlash('error', 'Nieprawidłowa rola.');
            return $this->redirectToRoute('admin_users');
        }
        
        $user->setRoles([$role]);
        $entityManager->flush();
        
        $this->addFlash('success', 'Zaktualizowano rolę użytkownika.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/orphanages', name: 'admin_orphanages')]
    public function orphanages(OrphanageRepository $orphanageRepository): Response
    {
        $orphanages = $orphanageRepository->findAll();
        
        return $this->render('admin/orphanages.html.twig', [
            'orphanages' => $orphanages,
        ]);
    }

    #[Route('/orphanages/{id}/toggle-verify', name: 'admin_orphanage_toggle_verify', methods: ['POST'])]
    public function toggleOrphanageVerify(Request $request, Orphanage $orphanage, EntityManagerInterface $entityManager): Response
    {
        $orphanage->setIsVerified(!$orphanage->isVerified());
        $entityManager->flush();
        
        $this->addFlash('success', 'Zaktualizowano status weryfikacji domu dziecka.');
        return $this->redirectToRoute('admin_orphanages');
    }

    #[Route('/dreams', name: 'admin_dreams')]
    public function dreams(DreamRepository $dreamRepository): Response
    {
        $dreams = $dreamRepository->findAll();
        
        return $this->render('admin/dreams.html.twig', [
            'dreams' => $dreams,
        ]);
    }

    #[Route('/dreams/{id}/toggle-status', name: 'admin_dream_toggle_status', methods: ['POST'])]
    public function toggleDreamStatus(Request $request, Dream $dream, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        $validStatuses = ['pending', 'verified', 'in_progress', 'fulfilled', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            $this->addFlash('error', 'Nieprawidłowy status.');
            return $this->redirectToRoute('admin_dreams');
        }
        
        $dream->setStatus($status);
        $entityManager->flush();
        
        $this->addFlash('success', 'Zaktualizowano status marzenia.');
        return $this->redirectToRoute('admin_dreams');
    }

    #[Route('/fulfillments', name: 'admin_fulfillments')]
    public function fulfillments(DreamFulfillmentRepository $fulfillmentRepository): Response
    {
        $fulfillments = $fulfillmentRepository->findAll();
        
        return $this->render('admin/fulfillments.html.twig', [
            'fulfillments' => $fulfillments,
        ]);
    }

    #[Route('/categories', name: 'admin_categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('admin/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categories/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function newCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(\App\Form\CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Kategoria została dodana.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/category_form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function editCategory(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(\App\Form\CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Kategoria została zaktualizowana.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/category_form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/categories/{id}/toggle', name: 'admin_category_toggle', methods: ['POST'])]
    public function toggleCategory(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $category->setIsActive(!$category->isActive());
        $entityManager->flush();

        $this->addFlash('success', 'Status kategorii został zmieniony.');
        return $this->redirectToRoute('admin_categories');
    }

    #[Route('/categories/{id}', name: 'admin_category_delete', methods: ['POST'])]
    public function deleteCategory(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('success', 'Kategoria została usunięta.');
        }

        return $this->redirectToRoute('admin_categories');
    }
}
