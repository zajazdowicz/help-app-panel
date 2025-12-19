<?php

namespace App\Controller;

use App\Entity\Dream;
use App\Entity\AffiliateClick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/affiliate')]
class AffiliateController extends AbstractController
{
    #[Route('/go/{id}', name: 'app_affiliate_go')]
    public function go(Dream $dream, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Rejestruj kliknięcie
        $click = new AffiliateClick();
        $click->setDream($dream);
        $click->setIpAddress($request->getClientIp());
        $click->setUserAgent($request->headers->get('User-Agent'));
        $click->setSessionId($request->getSession()->getId());
        
        $entityManager->persist($click);
        $entityManager->flush();
        
        // Przekieruj na link afiliacyjny lub produktowy
        $redirectUrl = $dream->getAffiliateUrl() ?: $dream->getProductUrl();
        return $this->redirect($redirectUrl);
    }
}
