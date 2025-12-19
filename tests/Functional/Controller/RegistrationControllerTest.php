<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterUser(): void
    {
        $client = static::createClient();
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
        $client = static::createClient();
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
        $entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $userRepo = $entityManager->getRepository(\App\Entity\User::class);
        $user = $userRepo->findOneBy(['email' => 'director@example.com']);
        $this->assertNotNull($user);
        $this->assertContains('ROLE_DIRECTOR', $user->getRoles());

        $orphanageRepo = $entityManager->getRepository(\App\Entity\Orphanage::class);
        $orphanage = $orphanageRepo->findOneBy(['contactEmail' => 'director@example.com']);
        $this->assertNotNull($orphanage);
        $this->assertFalse($orphanage->isVerified());
    }
}
