<?php

namespace App\Controller;

use App\Entity\Dream;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dreams')]
class DreamController extends AbstractController
{
    #[Route('/', name: 'app_dream_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $dreams = $entityManager
            ->getRepository(Dream::class)
            ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('dream/index.html.twig', [
            'dreams' => $dreams,
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
