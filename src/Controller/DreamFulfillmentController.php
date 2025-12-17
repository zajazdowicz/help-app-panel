<?php

namespace App\Controller;

use App\Entity\Dream;
use App\Entity\DreamFulfillment;
use App\Form\DreamFulfillmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dreams/{id}/fulfill')]
class DreamFulfillmentController extends AbstractController
{
    #[Route('', name: 'app_dream_fulfillment', methods: ['GET', 'POST'])]
    public function fulfill(Request $request, Dream $dream, EntityManagerInterface $entityManager): Response
    {
        if ($dream->isFullyFulfilled()) {
            $this->addFlash('warning', 'To marzenie jest już w pełni spełnione.');
            return $this->redirectToRoute('app_dream_show', ['id' => $dream->getId()]);
        }

        $fulfillment = new DreamFulfillment();
        $fulfillment->setDream($dream);
        // If user is logged in, associate them
        $currentUser = $this->getUser();
        if ($currentUser) {
            $fulfillment->setUser($currentUser);
        }

        $form = $this->createForm(DreamFulfillmentType::class, $fulfillment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Default status
            $fulfillment->setStatus(DreamFulfillment::STATUS_PENDING);
            
            // Ensure user is still associated (in case form changed something)
            if ($currentUser && !$fulfillment->getUser()) {
                $fulfillment->setUser($currentUser);
            }
            
            $entityManager->persist($fulfillment);

            // Update dream's fulfilled quantity
            $newFulfilled = $dream->getQuantityFulfilled() + $fulfillment->getQuantityFulfilled();
            $dream->setQuantityFulfilled($newFulfilled);

            $entityManager->flush();

            $this->addFlash('success', 'Dziękujemy za Twoją darowiznę!');
            return $this->redirectToRoute('app_dream_show', ['id' => $dream->getId()]);
        }

        return $this->render('dream_fulfillment/fulfill.html.twig', [
            'dream' => $dream,
            'form' => $form,
        ]);
    }

    #[Route('/fulfillment/{id}/edit-thanks', name: 'app_dream_fulfillment_edit_thanks', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_DIRECTOR')]
    public function editThanks(Request $request, DreamFulfillment $fulfillment, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orphanage = $user->getOrphanage();
        $dream = $fulfillment->getDream();

        // Sprawdź, czy darowizna dotyczy marzenia z domu dziecka dyrektora
        if ($dream->getOrphanage() !== $orphanage) {
            throw $this->createAccessDeniedException('Nie masz uprawnień do edycji tej darowizny.');
        }

        $form = $this->createForm(\App\Form\DreamFulfillmentThanksType::class, $fulfillment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Zaktualizowano podziękowanie i zdjęcie.');
            return $this->redirectToRoute('app_dream_show', ['id' => $dream->getId()]);
        }

        return $this->render('dream_fulfillment/edit_thanks.html.twig', [
            'fulfillment' => $fulfillment,
            'dream' => $dream,
            'form' => $form,
        ]);
    }
}
