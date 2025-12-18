<?php

namespace App\Controller;

use App\Entity\Child;
use App\Form\ChildType;
use App\Repository\ChildRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/director/children')]
#[IsGranted('PUBLIC_ACCESS')]
class ChildController extends AbstractController
{
    #[Route('/', name: 'director_child_index', methods: ['GET'])]
    public function index(ChildRepository $childRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            // This should not happen because of IsGranted, but just in case
            throw $this->createAccessDeniedException('Nie jesteś zalogowany.');
        }
        
        // Debug: dump user and roles (only in dev)
        if ($this->getParameter('kernel.environment') === 'dev') {
            dump($user);
            dump($user->getRoles());
            dump($this->isGranted('ROLE_DIRECTOR'));
            // exit; // uncomment to see dump
        }
        
        // Sprawdź, czy użytkownik ma rolę ROLE_DIRECTOR
        if (!$this->isGranted('ROLE_DIRECTOR')) {
            throw $this->createAccessDeniedException('Brak wymaganej roli ROLE_DIRECTOR.');
        }
        
        $orphanage = $user->getOrphanage();
        
        // Jeśli użytkownik nie ma przypisanego domu dziecka, ale ma rolę ROLE_DIRECTOR (np. admin)
        if (!$orphanage) {
            if ($this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_DIRECTOR')) {
                // Super admin może przeglądać, ale nie ma przypisanego domu dziecka
                $this->addFlash('info', 'Jesteś zalogowany jako Super Admin. Nie masz przypisanego domu dziecka, więc nie możesz dodawać dzieci ani marzeń.');
                $children = [];
            } else {
                $this->addFlash('warning', 'Nie jesteś przypisany do żadnego domu dziecka. Skontaktuj się z administratorem.');
                return $this->redirectToRoute('app_home');
            }
        } else {
            if (!$orphanage->isVerified()) {
                $this->addFlash('warning', 'Twój dom dziecka nie jest jeszcze zweryfikowany.');
            }
            $children = $childRepository->findBy(['orphanage' => $orphanage], ['firstName' => 'ASC']);
        }
        
        return $this->render('child/index.html.twig', [
            'children' => $children,
            'orphanage' => $orphanage,
        ]);
    }

    #[Route('/new', name: 'director_child_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        if (!$orphanage) {
            $this->addFlash('warning', 'Nie jesteś przypisany do żadnego domu dziecka. Skontaktuj się z administratorem.');
            return $this->redirectToRoute('app_home');
        }
        
        if (!$orphanage->isVerified()) {
            $this->addFlash('warning', 'Twój dom dziecka nie jest jeszcze zweryfikowany. Nie możesz dodawać dzieci.');
            return $this->redirectToRoute('app_home');
        }
        
        $child = new Child();
        $child->setOrphanage($orphanage);
        
        $form = $this->createForm(ChildType::class, $child);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($child);
            $entityManager->flush();
            
            $this->addFlash('success', 'Dodano nowe dziecko.');
            return $this->redirectToRoute('director_child_index');
        }
        
        return $this->render('child/new.html.twig', [
            'child' => $child,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'director_child_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Child $child, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        if (!$orphanage) {
            $this->addFlash('warning', 'Nie jesteś przypisany do żadnego domu dziecka. Skontaktuj się z administratorem.');
            return $this->redirectToRoute('app_home');
        }
        
        if (!$orphanage->isVerified()) {
            $this->addFlash('warning', 'Twój dom dziecka nie jest jeszcze zweryfikowany.');
            return $this->redirectToRoute('app_home');
        }
        
        // Sprawdź, czy dziecko należy do domu dziecka dyrektora
        if ($child->getOrphanage() !== $orphanage) {
            throw $this->createAccessDeniedException('Nie masz uprawnień do edycji tego dziecka.');
        }
        
        $form = $this->createForm(ChildType::class, $child);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Zaktualizowano dane dziecka.');
            return $this->redirectToRoute('director_child_index');
        }
        
        return $this->render('child/edit.html.twig', [
            'child' => $child,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'director_child_delete', methods: ['POST'])]
    public function delete(Request $request, Child $child, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        
        if (!$orphanage) {
            $this->addFlash('warning', 'Nie jesteś przypisany do żadnego domu dziecka. Skontaktuj się z administratorem.');
            return $this->redirectToRoute('app_home');
        }
        
        if (!$orphanage->isVerified()) {
            $this->addFlash('warning', 'Twój dom dziecka nie jest jeszcze zweryfikowany.');
            return $this->redirectToRoute('app_home');
        }
        
        // Sprawdź, czy dziecko należy do domu dziecka dyrektora
        if ($child->getOrphanage() !== $orphanage) {
            throw $this->createAccessDeniedException('Nie masz uprawnień do usunięcia tego dziecka.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$child->getId(), $request->request->get('_token'))) {
            $entityManager->remove($child);
            $entityManager->flush();
            $this->addFlash('success', 'Usunięto dziecko.');
        }
        
        return $this->redirectToRoute('director_child_index');
    }
}
