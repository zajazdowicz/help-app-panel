<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testAdminDashboardRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/');

        // Should redirect to login
        $this->assertResponseRedirects();
    }

    public function testAdminDashboardAccessWithAdminRole(): void
    {
        $client = static::createClient();

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
