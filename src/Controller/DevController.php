<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Orphanage;
use App\Entity\Child;
use App\Entity\Dream;
use App\Entity\DreamFulfillment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/dev')]
class DevController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/fill-data', name: 'dev_fill_data')]
    public function fillData(): Response
    {
        // Allow only in dev environment
        if ($this->getParameter('kernel.environment') !== 'dev') {
            throw $this->createNotFoundException();
        }

        // 1. Users
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setUsername('admin');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'password123'));
        $adminUser->setIsVerified(true);
        $this->entityManager->persist($adminUser);

        $directorUser = new User();
        $directorUser->setEmail('director@example.com');
        $directorUser->setUsername('director');
        $directorUser->setRoles(['ROLE_DIRECTOR']);
        $directorUser->setPassword($this->passwordHasher->hashPassword($directorUser, 'password123'));
        $directorUser->setIsVerified(true);
        $this->entityManager->persist($directorUser);

        $regularUser = new User();
        $regularUser->setEmail('user@example.com');
        $regularUser->setUsername('user');
        $regularUser->setRoles(['ROLE_USER']);
        $regularUser->setPassword($this->passwordHasher->hashPassword($regularUser, 'password123'));
        $regularUser->setIsVerified(true);
        $this->entityManager->persist($regularUser);

        // 2. Orphanage
        $orphanage = new Orphanage();
        $orphanage->setName('Dom Dziecka w Warszawie');
        $orphanage->setAddress('ul. Przykładowa 123');
        $orphanage->setCity('Warszawa');
        $orphanage->setRegion('Mazowieckie');
        $orphanage->setPostalCode('00-001');
        $orphanage->setContactEmail('kontakt@warszawa.dd.pl');
        $orphanage->setContactPhone('+48 123 456 789');
        $orphanage->setIsVerified(true);
        $orphanage->setDirector($directorUser);
        $this->entityManager->persist($orphanage);

        // 3. Children
        $child1 = new Child();
        $child1->setFirstName('Jan');
        $child1->setAge(10);
        $child1->setDescription('Jan marzy o nowym rowerze, aby jeździć ze znajomymi.');
        $child1->setOrphanage($orphanage);
        $child1->setIsVerified(true);
        $this->entityManager->persist($child1);

        $child2 = new Child();
        $child2->setFirstName('Anna');
        $child2->setAge(14);
        $child2->setDescription('Anna chce zestaw artystyczny do malowania.');
        $child2->setOrphanage($orphanage);
        $child2->setIsVerified(true);
        $this->entityManager->persist($child2);

        // 4. Dreams
        $dream1 = new Dream();
        $dream1->setChild($child1);
        $dream1->setOrphanage($orphanage);
        $dream1->setProductUrl('https://example.com/rower');
        $dream1->setProductTitle('Rower górski 24 cale');
        $dream1->setProductPrice('599.99');
        $dream1->setProductCategory('Sport');
        $dream1->setDescription('Rower pomógłby Janowi w codziennych dojazdach do szkoły i rekreacji.');
        // set status bypassing validation
        (function() use ($dream1) {
            $reflection = new \ReflectionClass($dream1);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream1, 'approved');
        })();
        $dream1->setQuantityNeeded(1);
        $dream1->setQuantityFulfilled(0);
        $dream1->setIsUrgent(false);
        $this->entityManager->persist($dream1);

        $dream2 = new Dream();
        $dream2->setChild($child2);
        $dream2->setOrphanage($orphanage);
        $dream2->setProductUrl('https://example.com/zestaw-malarski');
        $dream2->setProductTitle('Zestaw malarski 100 elementów');
        $dream2->setProductPrice('129.50');
        $dream2->setProductCategory('Edukacja');
        $dream2->setDescription('Anna chce rozwijać talent plastyczny.');
        $dream2->setStatus('pending');
        $dream2->setQuantityNeeded(1);
        $dream2->setQuantityFulfilled(0);
        $dream2->setIsUrgent(true);
        $this->entityManager->persist($dream2);

        $dream3 = new Dream();
        $dream3->setChild($child1);
        $dream3->setOrphanage($orphanage);
        $dream3->setProductUrl('https://example.com/ksiazki');
        $dream3->setProductTitle('Komiksy przygodowe');
        $dream3->setProductPrice('89.00');
        $dream3->setProductCategory('Książki');
        $dream3->setDescription('Jan lubi czytać komiksy.');
        // set status bypassing validation
        (function() use ($dream3) {
            $reflection = new \ReflectionClass($dream3);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream3, 'approved');
        })();
        $dream3->setQuantityNeeded(5);
        $dream3->setQuantityFulfilled(2);
        $dream3->setIsUrgent(false);
        $this->entityManager->persist($dream3);

        // 5. DreamFulfillment
        $fulfillment1 = new DreamFulfillment();
        $fulfillment1->setDream($dream3);
        $fulfillment1->setDonorName('Anonimowy Darczyńca');
        $fulfillment1->setDonorEmail('anon@example.com');
        $fulfillment1->setDonorNickname('Anonim');
        $fulfillment1->setIsAnonymous(true);
        $fulfillment1->setStatus('completed');
        $fulfillment1->setQuantityFulfilled(2);
        $this->entityManager->persist($fulfillment1);

        $fulfillment2 = new DreamFulfillment();
        $fulfillment2->setDream($dream3);
        $fulfillment2->setDonorName('Jan Kowalski');
        $fulfillment2->setDonorEmail('jan.kowalski@example.com');
        $fulfillment2->setDonorNickname('Janek');
        $fulfillment2->setIsAnonymous(false);
        $fulfillment2->setStatus('pending');
        $fulfillment2->setQuantityFulfilled(1);
        $this->entityManager->persist($fulfillment2);

        $this->entityManager->flush();

        return new Response('Dane testowe zostały dodane pomyślnie.');
    }
}
