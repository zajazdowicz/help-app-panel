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
}
