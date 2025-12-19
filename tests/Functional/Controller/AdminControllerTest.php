<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    private static $schemaCreated = false;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
        self::$schemaCreated = true;
    }

    public function testAdminDashboardRequiresLogin(): void
    {
        $client = static::createClient(['environment' => 'test']);
        $client->request('GET', '/admin/');

        // Should redirect to login
        $this->assertResponseRedirects();
    }

    public function testAdminDashboardAccessWithAdminRole(): void
    {
        $client = static::createClient(['environment' => 'test']);

        // Create an admin user
        $entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setUsername('admin');
        $user->setPassword('$2y$13$...'); // dummy hash
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        // Simulate login
        $client->loginUser($user);

        $client->request('GET', '/admin/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Panel administratora');
    }
}
