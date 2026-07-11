<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface; // Import UrlGeneratorInterface

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    // Enregistrer un nouveau compte utilisateur
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator // Inject UrlGeneratorInterface
    ): Response {
        $user = new Compte();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var Compte $compte */
                $compte = $form->getData();

                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                // hacher le mot de passe en clair
                $compte->setPassword($userPasswordHasher->hashPassword($compte, $plainPassword));

                // définir le rôle utilisateur par défaut
                $compte->setRole('ROLE_USER');

                $entityManager->persist($compte);
                $entityManager->flush();

                // générer une URL signée et l'envoyer à l'utilisateur
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@hotel-reservation.com', 'Hotel-Reservation'))
                        ->to((string) $user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vérifier votre e-mail pour confirmer votre adresse.');

                // Get the target path from the request, or default to app_reservation_search
                $targetPath = $request->query->get('_target_path', $urlGenerator->generate('app_reservation_search'));

                // Log the user in and redirect to the target path
                $security->login($user, 'form_login', 'main');

                return $this->redirect($targetPath);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Un compte avec cet e-mail existe déjà.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du compte : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    // Vérifier l'adresse e-mail de l'utilisateur
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // valider le lien de confirmation d'e-mail et marquer l'utilisateur comme vérifié
        try {
            /** @var Compte $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
