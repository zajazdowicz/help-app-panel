<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Orphanage;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class RegistrationController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $isDirector = $form->get('isDirector')->getData();
            $roles = ['ROLE_USER'];
            if ($isDirector) {
                $roles[] = 'ROLE_DIRECTOR';
            }
            $user->setRoles($roles);
            $user->setIsVerified(true); // na razie automatyczna weryfikacja, można zmienić

            $entityManager->persist($user);

            // Jeśli to dyrektor, utwórz pusty dom dziecka (niezweryfikowany)
            if ($isDirector) {
                $orphanage = new Orphanage();
                $orphanage->setName('Nowy dom dziecka - do uzupełnienia');
                $orphanage->setAddress('');
                $orphanage->setCity('');
                $orphanage->setRegion('');
                $orphanage->setPostalCode('');
                $orphanage->setContactEmail($user->getEmail());
                $orphanage->setContactPhone('');
                $orphanage->setIsVerified(false);
                $orphanage->setDirector($user);

                $entityManager->persist($orphanage);
            }

            $entityManager->flush();

            // Wyślij email powitalny
            try {
                $fromEmail = $_ENV['MAILER_FROM'] ?? 'noreply@helpdreams.pl';
                $email = (new TemplatedEmail())
                    ->from($fromEmail)
                    ->to($user->getEmail())
                    ->subject('Witamy w HelpDreams!')
                    ->htmlTemplate('emails/user_registered.html.twig')
                    ->context([
                        'user' => $user,
                        'isDirector' => $isDirector,
                    ]);

                $this->mailer->send($email);
            } catch (\Exception $e) {
                // Log error, but don't break registration
                // In production, you should log this
            }

            $this->addFlash('success', 'Rejestracja zakończona sukcesem. Możesz się teraz zalogować.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
