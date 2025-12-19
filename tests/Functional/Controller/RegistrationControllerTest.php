<?php

namespace App\Tests\Functional\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private static $schemaCreated = false;
    private $entityManager;

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

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test']);
        $container = $this->client->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

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

    public function testRegisterUser(): void
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Rejestracja');

        $form = $crawler->selectButton('Zarejestruj się')->form();
        $form['registration_form[email]'] = 'testuser@example.com';
        $form['registration_form[username]'] = 'testuser';
        $form['registration_form[plainPassword]'] = 'password123';
        $form['registration_form[accountType]'] = 'user';
        $form['registration_form[agreeTerms]'] = true;

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testRegisterDirector(): void
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Zarejestruj się')->form();
        $form['registration_form[email]'] = 'director@example.com';
        $form['registration_form[username]'] = 'director';
        $form['registration_form[plainPassword]'] = 'password123';
        $form['registration_form[accountType]'] = 'director';
        $form['registration_form[agreeTerms]'] = true;

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        // Check that Orphanage was created
        $userRepo = $this->entityManager->getRepository(\App\Entity\User::class);
        $user = $userRepo->findOneBy(['email' => 'director@example.com']);
        $this->assertNotNull($user);
        $this->assertContains('ROLE_DIRECTOR', $user->getRoles());

        $orphanageRepo = $this->entityManager->getRepository(\App\Entity\Orphanage::class);
        $orphanage = $orphanageRepo->findOneBy(['contactEmail' => 'director@example.com']);
        $this->assertNotNull($orphanage);
        $this->assertFalse($orphanage->isVerified());
    }
}
