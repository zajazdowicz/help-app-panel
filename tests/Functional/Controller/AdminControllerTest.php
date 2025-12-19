<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test']);
        $container = $this->client->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        // Create schema for each test (SQLite in-memory is fast)
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

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

    public function testAdminDashboardRequiresLogin(): void
    {
        $client = $this->client;
        $client->request('GET', '/admin/');

        // Should redirect to login
        $this->assertResponseRedirects();
    }

    public function testAdminDashboardAccessWithAdminRole(): void
    {
        $client = $this->client;

        // Create an admin user
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setUsername('admin');
        $user->setPassword('$2y$13$...'); // dummy hash
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Simulate login
        $client->loginUser($user);

        $client->request('GET', '/admin/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Panel administratora');
    }
}
