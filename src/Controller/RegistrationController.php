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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur gérant l'enregistrement et la vérification d'email des utilisateurs.
 *
 * Ce contrôleur est responsable de l'affichage du formulaire d'inscription,
 * de la création de nouveaux comptes, de l'envoi d'emails de confirmation
 * et de la gestion du processus de vérification d'email.
 */
class RegistrationController extends AbstractController
{
    /**
     * Constructeur du contrôleur d'enregistrement.
     *
     * @param EmailVerifier $emailVerifier Le service de vérification d'email.
     */
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    /**
     * Enregistre un nouveau compte utilisateur.
     *
     * Gère la soumission du formulaire d'inscription, le hachage du mot de passe,
     * la persistance du nouvel utilisateur et l'envoi de l'email de confirmation.
     * En cas de succès, l'utilisateur est connecté et redirigé.
     *
     * @param Request $request La requête HTTP.
     * @param UserPasswordHasherInterface $userPasswordHasher Le service de hachage de mot de passe.
     * @param Security $security Le service de sécurité pour la connexion automatique.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param UrlGeneratorInterface $urlGenerator Le générateur d'URL.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant après succès/échec.
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        LoggerInterface $logger
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
                        ->subject($translator->trans('registration.email.subject', [], 'app'))
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                $this->addFlash('success', $translator->trans('registration.success', [], 'app'));

                // Get the target path from the request, or default to app_reservation_search
                $targetPath = $request->query->get('_target_path', $urlGenerator->generate('app_reservation_search'));

                // Log the user in and redirect to the target path
                $security->login($user, 'form_login', 'main');

                return $this->redirect($targetPath);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Registration failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('registration.error.email_exists', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Registration failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('registration.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during registration: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('registration.error.unexpected', [], 'app'));
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Vérifie l'adresse e-mail de l'utilisateur.
     *
     * Cette action est appelée lorsque l'utilisateur clique sur le lien de vérification
     * envoyé par email. Elle valide le lien et marque l'utilisateur comme vérifié.
     *
     * @param Request $request La requête HTTP contenant les paramètres de vérification.
     * @param TranslatorInterface $translator Le service de traduction.
     * @return Response Une réponse de redirection après la vérification ou en cas d'erreur.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas authentifié.
     */
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

        $this->addFlash('success', $translator->trans('email_verification.success', [], 'app'));

        return $this->redirectToRoute('app_register');
    }
}
