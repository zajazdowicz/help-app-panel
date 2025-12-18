<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Orphanage;
use App\Entity\Child;
use App\Entity\Dream;
use App\Entity\DreamFulfillment;
use App\Entity\AffiliateClick;
use App\Entity\AffiliateConversion;
use App\Enum\DreamStatus;
use App\Enum\DreamFulfillmentStatus;
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

    #[Route('/fix-roles', name: 'dev_fix_roles')]
    public function fixRoles(): Response
    {
        // Allow only in dev environment
        if ($this->getParameter('kernel.environment') !== 'dev') {
            throw $this->createNotFoundException();
        }

        $users = $this->entityManager->getRepository(User::class)->findAll();
        $fixed = 0;
        foreach ($users as $user) {
            $roles = $user->getRoles();
            $needsFix = false;
            // Jeśli znajdziemy błędną rolę ROLE_SUPER_ADMINR, zamień na poprawne
            if (in_array('ROLE_SUPER_ADMINR', $roles, true)) {
                $roles = array_filter($roles, fn($r) => $r !== 'ROLE_SUPER_ADMINR');
                $roles = array_unique(array_merge($roles, ['ROLE_ADMIN', 'ROLE_DIRECTOR', 'ROLE_USER']));
                $needsFix = true;
            }
            // Upewnij się, że ROLE_USER zawsze istnieje
            if (!in_array('ROLE_USER', $roles, true)) {
                $roles[] = 'ROLE_USER';
                $needsFix = true;
            }
            if ($needsFix) {
                $user->setRoles(array_values($roles));
                $fixed++;
            }
        }
        $this->entityManager->flush();
        return new Response("Naprawiono $fixed użytkowników.");
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

        // Dodajemy więcej użytkowników
        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setUsername('user2');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password123'));
        $user2->setIsVerified(true);
        $this->entityManager->persist($user2);

        $director2 = new User();
        $director2->setEmail('director2@example.com');
        $director2->setUsername('director2');
        $director2->setRoles(['ROLE_DIRECTOR']);
        $director2->setPassword($this->passwordHasher->hashPassword($director2, 'password123'));
        $director2->setIsVerified(true);
        $this->entityManager->persist($director2);

        // 2. Orphanages
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

        $orphanage2 = new Orphanage();
        $orphanage2->setName('Dom Dziecka w Krakowie');
        $orphanage2->setAddress('ul. Krakowska 456');
        $orphanage2->setCity('Kraków');
        $orphanage2->setRegion('Małopolskie');
        $orphanage2->setPostalCode('30-001');
        $orphanage2->setContactEmail('kontakt@krakow.dd.pl');
        $orphanage2->setContactPhone('+48 987 654 321');
        $orphanage2->setIsVerified(false); // Niezweryfikowany
        $orphanage2->setDirector($director2);
        $this->entityManager->persist($orphanage2);

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

        $child3 = new Child();
        $child3->setFirstName('Piotr');
        $child3->setAge(12);
        $child3->setDescription('Piotr lubi grać w piłkę nożną.');
        $child3->setOrphanage($orphanage2);
        $child3->setIsVerified(true);
        $this->entityManager->persist($child3);

        $child4 = new Child();
        $child4->setFirstName('Kasia');
        $child4->setAge(9);
        $child4->setDescription('Kasia uwielbia czytać książki przygodowe.');
        $child4->setOrphanage($orphanage2);
        $child4->setIsVerified(true);
        $this->entityManager->persist($child4);

        // 4. Dreams
        $dream1 = new Dream();
        $dream1->setChild($child1);
        $dream1->setOrphanage($orphanage);
        $dream1->setProductUrl('https://example.com/rower');
        $dream1->setProductTitle('Rower górski 24 cale');
        $dream1->setProductPrice('599.99');
        $dream1->setDescription('Rower pomógłby Janowi w codziennych dojazdach do szkoły i rekreacji.');
        // set status bypassing validation
        (function() use ($dream1) {
            $reflection = new \ReflectionClass($dream1);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream1, DreamStatus::VERIFIED);
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
        $dream2->setDescription('Anna chce rozwijać talent plastyczny.');
        // set status bypassing validation
        (function() use ($dream2) {
            $reflection = new \ReflectionClass($dream2);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream2, DreamStatus::PENDING);
        })();
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
        $dream3->setDescription('Jan lubi czytać komiksy.');
        // set status bypassing validation
        (function() use ($dream3) {
            $reflection = new \ReflectionClass($dream3);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream3, DreamStatus::VERIFIED);
        })();
        $dream3->setQuantityNeeded(5);
        $dream3->setQuantityFulfilled(2);
        $dream3->setIsUrgent(false);
        $this->entityManager->persist($dream3);

        $dream4 = new Dream();
        $dream4->setChild($child3);
        $dream4->setOrphanage($orphanage2);
        $dream4->setProductUrl('https://example.com/pilka');
        $dream4->setProductTitle('Piłka nożna profesjonalna');
        $dream4->setProductPrice('199.00');
        $dream4->setDescription('Piotr marzy o profesjonalnej piłce do treningów.');
        // set status bypassing validation
        (function() use ($dream4) {
            $reflection = new \ReflectionClass($dream4);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream4, DreamStatus::VERIFIED);
        })();
        $dream4->setQuantityNeeded(1);
        $dream4->setQuantityFulfilled(1);
        $dream4->setIsUrgent(false);
        $this->entityManager->persist($dream4);

        $dream5 = new Dream();
        $dream5->setChild($child4);
        $dream5->setOrphanage($orphanage2);
        $dream5->setProductUrl('https://example.com/ksiazka');
        $dream5->setProductTitle('Seria książek "Harry Potter"');
        $dream5->setProductPrice('350.00');
        $dream5->setDescription('Kasia chciałaby przeczytać całą serię Harry\'ego Pottera.');
        // set status bypassing validation
        (function() use ($dream5) {
            $reflection = new \ReflectionClass($dream5);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($dream5, DreamStatus::FULFILLED);
        })();
        $dream5->setQuantityNeeded(1);
        $dream5->setQuantityFulfilled(1);
        $dream5->setIsUrgent(false);
        $this->entityManager->persist($dream5);

        // 5. DreamFulfillment
        $fulfillment1 = new DreamFulfillment();
        $fulfillment1->setDream($dream3);
        $fulfillment1->setDonorName('Anonimowy Darczyńca');
        $fulfillment1->setDonorEmail('anon@example.com');
        $fulfillment1->setDonorNickname('Anonim');
        $fulfillment1->setIsAnonymous(true);
        // set status bypassing validation
        (function() use ($fulfillment1) {
            $reflection = new \ReflectionClass($fulfillment1);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($fulfillment1, DreamFulfillmentStatus::CONFIRMED);
        })();
        $fulfillment1->setQuantityFulfilled(2);
        $this->entityManager->persist($fulfillment1);

        $fulfillment2 = new DreamFulfillment();
        $fulfillment2->setDream($dream3);
        $fulfillment2->setDonorName('Jan Kowalski');
        $fulfillment2->setDonorEmail('jan.kowalski@example.com');
        $fulfillment2->setDonorNickname('Janek');
        $fulfillment2->setIsAnonymous(false);
        // set status bypassing validation
        (function() use ($fulfillment2) {
            $reflection = new \ReflectionClass($fulfillment2);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($fulfillment2, DreamFulfillmentStatus::PENDING);
        })();
        $fulfillment2->setQuantityFulfilled(1);
        $this->entityManager->persist($fulfillment2);

        $fulfillment3 = new DreamFulfillment();
        $fulfillment3->setDream($dream4);
        $fulfillment3->setDonorName('Maria Nowak');
        $fulfillment3->setDonorEmail('maria.nowak@example.com');
        $fulfillment3->setDonorNickname('Maria');
        $fulfillment3->setIsAnonymous(false);
        // set status bypassing validation
        (function() use ($fulfillment3) {
            $reflection = new \ReflectionClass($fulfillment3);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($fulfillment3, DreamFulfillmentStatus::CONFIRMED);
        })();
        $fulfillment3->setQuantityFulfilled(1);
        $fulfillment3->setChildPhotoUrl('https://example.com/photo.jpg');
        $fulfillment3->setChildMessage('Dziękuję za piłkę! Piotr');
        $this->entityManager->persist($fulfillment3);

        $fulfillment4 = new DreamFulfillment();
        $fulfillment4->setDream($dream5);
        $fulfillment4->setDonorName('Fundacja Pomocna Dłoń');
        $fulfillment4->setDonorEmail('fundacja@example.com');
        $fulfillment4->setDonorNickname('Fundacja');
        $fulfillment4->setIsAnonymous(false);
        // set status bypassing validation
        (function() use ($fulfillment4) {
            $reflection = new \ReflectionClass($fulfillment4);
            $property = $reflection->getProperty('status');
            $property->setAccessible(true);
            $property->setValue($fulfillment4, DreamFulfillmentStatus::CONFIRMED);
        })();
        $fulfillment4->setQuantityFulfilled(1);
        $fulfillment4->setChildPhotoUrl('https://example.com/kasia.jpg');
        $fulfillment4->setChildMessage('Kasia jest bardzo szczęśliwa z nowych książek!');
        $this->entityManager->persist($fulfillment4);

        $this->entityManager->flush();

        return new Response('Dane testowe zostały dodane pomyślnie. Utworzono: 5 użytkowników, 2 domy dziecka, 4 dzieci, 5 marzeń, 4 darowizny.');
    }
}
