<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Dream;
use App\Entity\Orphanage;
use App\Entity\Child;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AffiliateControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGoRedirectsToAffiliateUrl(): void
    {
        // Create necessary entities
        $orphanage = new Orphanage();
        $orphanage->setName('Test Orphanage');
        $orphanage->setCity('Warsaw');
        $orphanage->setIsVerified(true);
        $this->entityManager->persist($orphanage);

        $child = new Child();
        $child->setFirstName('Test');
        $child->setAge(10);
        $child->setOrphanage($orphanage);
        $this->entityManager->persist($child);

        $category = new Category();
        $category->setName('Test Category');
        $category->setSlug('test-category');
        $category->setIsActive(true);
        $this->entityManager->persist($category);

        $dream = new Dream();
        $dream->setProductTitle('Test Product');
        $dream->setProductUrl('https://example.com/product');
        $dream->setProductPrice('100.00');
        $dream->setDescription('Test description');
        $dream->setQuantityNeeded(1);
        $dream->setStatus('pending');
        $dream->setOrphanage($orphanage);
        $dream->setChild($child);
        $dream->setCategory($category);
        $dream->setAffiliatePartner('allegro');
        $dream->setAffiliateTrackingId('test123');
        $dream->setOriginalProductUrl('https://allegro.pl/item');
        $dream->setAffiliateUrl('https://allegro.pl/item?aff_id=test123');
        $this->entityManager->persist($dream);

        $this->entityManager->flush();

        $this->client->request('GET', '/affiliate/go/' . $dream->getId());

        $this->assertResponseRedirects('https://allegro.pl/item?aff_id=test123');

        // Check that a click was recorded
        $clickRepo = $this->entityManager->getRepository(\App\Entity\AffiliateClick::class);
        $clicks = $clickRepo->findBy(['dream' => $dream]);
        $this->assertCount(1, $clicks);
    }

    public function testGoWithoutAffiliateUrlRedirectsToProductUrl(): void
    {
        $orphanage = new Orphanage();
        $orphanage->setName('Test Orphanage 2');
        $orphanage->setCity('Krakow');
        $orphanage->setIsVerified(true);
        $this->entityManager->persist($orphanage);

        $child = new Child();
        $child->setFirstName('Test2');
        $child->setAge(12);
        $child->setOrphanage($orphanage);
        $this->entityManager->persist($child);

        $category = new Category();
        $category->setName('Test Category 2');
        $category->setSlug('test-category-2');
        $category->setIsActive(true);
        $this->entityManager->persist($category);

        $dream = new Dream();
        $dream->setProductTitle('Test Product 2');
        $dream->setProductUrl('https://example.com/product2');
        $dream->setProductPrice('200.00');
        $dream->setDescription('Test description 2');
        $dream->setQuantityNeeded(2);
        $dream->setStatus('pending');
        $dream->setOrphanage($orphanage);
        $dream->setChild($child);
        $dream->setCategory($category);
        // No affiliate fields
        $this->entityManager->persist($dream);

        $this->entityManager->flush();

        $this->client->request('GET', '/affiliate/go/' . $dream->getId());

        $this->assertResponseRedirects('https://example.com/product2');
    }
}
