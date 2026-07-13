<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur gérant le processus de réinitialisation de mot de passe.
 *
 * Ce contrôleur implémente les fonctionnalités nécessaires pour permettre
 * aux utilisateurs de demander une réinitialisation de leur mot de passe
 * et de le modifier via un lien sécurisé envoyé par email.
 */
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    /**
     * Constructeur du contrôleur de réinitialisation de mot de passe.
     *
     * @param ResetPasswordHelperInterface $resetPasswordHelper Le service d'aide à la réinitialisation de mot de passe.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     */
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Affiche et traite le formulaire de demande de réinitialisation de mot de passe.
     *
     * Permet à l'utilisateur de saisir son adresse email pour recevoir un lien
     * de réinitialisation.
     *
     * @param Request $request La requête HTTP.
     * @param MailerInterface $mailer Le service d'envoi d'emails.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator,
                $logger
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Page de confirmation après qu'un utilisateur a demandé une réinitialisation de mot de passe.
     *
     * Cette page informe l'utilisateur qu'un email a été envoyé (ou non, pour des raisons de sécurité).
     *
     * @return Response Une réponse HTTP affichant la page de confirmation.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Génère un jeton factice si l'utilisateur tente d'accéder directement à cette page.
        // Cela garantit que l'utilisateur ne peut pas déduire si un compte est enregistré.
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            // Redirige vers la page de demande de mot de passe si aucun jeton n'est trouvé en session.
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Valide et traite l'URL de réinitialisation sur laquelle l'utilisateur a cliqué dans son email.
     *
     * Permet à l'utilisateur de définir un nouveau mot de passe après avoir validé le jeton.
     *
     * @param Request $request La requête HTTP.
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param string|null $token Le jeton de réinitialisation de mot de passe.
     * @return Response Une réponse HTTP affichant le formulaire de réinitialisation ou redirigeant.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            // Nous stockons le jeton en session et le retirons de l'URL, pour éviter que l'URL ne soit
            // chargée dans un navigateur et potentiellement fuir le jeton à des scripts JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException($translator->trans('reset_password.error.no_token', [], 'app'));
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle'),
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le jeton est valide ; permet à l'utilisateur de changer son mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un objet ResetPasswordRequest ne doit être utilisé qu'une seule fois, nous le supprimons.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode (hache) le mot de passe en clair et le définit.
            /** @var Compte $user */
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // La session est nettoyée après que le mot de passe a été modifié.
            $this->cleanSessionAfterReset();

            $this->addFlash('success', $translator->trans('reset_password.success', [], 'app'));

            return $this->redirectToRoute('app_home'); // Rediriger vers la page d'accueil ou de connexion
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * Traite l'envoi de l'email de réinitialisation de mot de passe.
     *
     * Recherche l'utilisateur par email, génère un jeton de réinitialisation
     * et envoie un email contenant le lien de réinitialisation.
     *
     * @param string $emailFormData L'adresse email soumise par l'utilisateur.
     * @param MailerInterface $mailer Le service d'envoi d'emails.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return RedirectResponse Une réponse de redirection vers la page de vérification d'email.
     */
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator, LoggerInterface $logger): RedirectResponse
    {
        $user = $this->entityManager->getRepository(Compte::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne pas révéler si un compte utilisateur a été trouvé ou non.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $logger->error('Failed to generate password reset token: ' . $e->getMessage());
            $this->addFlash('reset_password_error', $translator->trans($e->getReason(), [], 'ResetPasswordBundle'));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@hotel-reservation.com', 'Hotel-Reservation'))
            ->to($user->getEmail())
            ->subject($translator->trans('reset_password.email.subject', [], 'app'))
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $logger->error('Failed to send password reset email: ' . $e->getMessage());
            $this->addFlash('reset_password_error', $translator->trans('reset_password.email.error.send_failed', [], 'app'));
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Stocke l'objet jeton en session pour le récupérer dans checkEmail()
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
