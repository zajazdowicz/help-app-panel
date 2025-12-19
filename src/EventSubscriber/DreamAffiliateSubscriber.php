<?php

namespace App\EventSubscriber;

use App\Entity\Dream;
use App\Service\AffiliateLinkGenerator;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DreamAffiliateSubscriber implements EventSubscriberInterface
{
    public function __construct(private AffiliateLinkGenerator $linkGenerator)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->handleAffiliateUrl($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->handleAffiliateUrl($args);
    }

    private function handleAffiliateUrl(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Dream) {
            return;
        }

        // Jeśli affiliateUrl jest już ustawiony ręcznie, nie nadpisuj
        if ($entity->getAffiliateUrl() !== null && $entity->getAffiliateUrl() !== '') {
            return;
        }

        $originalUrl = $entity->getOriginalProductUrl();
        $partner = $entity->getAffiliatePartner();
        $trackingId = $entity->getAffiliateTrackingId();

        if ($originalUrl && $partner) {
            $affiliateUrl = $this->linkGenerator->generateForDream($originalUrl, $partner, $trackingId);
            $entity->setAffiliateUrl($affiliateUrl);
        }
    }
}
