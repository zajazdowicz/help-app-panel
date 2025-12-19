<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Dream;
use App\Entity\Orphanage;
use App\Entity\Child;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AffiliateControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private static $schemaCreated = false;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test']);
        $container = $this->client->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        // Create schema only once per test class
        if (!self::$schemaCreated) {
            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
            self::$schemaCreated = true;
        }

        // Begin a transaction for each test
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback the transaction to clean up the database
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollBack();
        }
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testGoRedirectsToAffiliateUrl(): void
    {
        // Create necessary entities
        $orphanage = new Orphanage();
        $orphanage->setName('Test Orphanage');
        $orphanage->setAddress('Test Address');
        $orphanage->setCity('Warsaw');
        $orphanage->setRegion('Mazowieckie');
        $orphanage->setPostalCode('00-001');
        $orphanage->setContactEmail('test@example.com');
        $orphanage->setContactPhone('+48123456789');
        $orphanage->setIsVerified(true);
        // Nie ustawiamy director, bo nie jest potrzebny w teście
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
        $orphanage->setAddress('Test Address 2');
        $orphanage->setCity('Krakow');
        $orphanage->setRegion('Malopolskie');
        $orphanage->setPostalCode('30-001');
        $orphanage->setContactEmail('test2@example.com');
        $orphanage->setContactPhone('+48123456780');
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
