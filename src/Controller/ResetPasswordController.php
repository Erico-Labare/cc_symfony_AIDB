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
use Symfony\Component\Mime\Address; // Added for Address class

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    // Removed the explicit generateFakeResetToken method, will handle directly in checkEmail

    /**
     * Display & process form to request a password reset.
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
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user ever tries to access this page directly.
        // This ensures that the user can't deduce whether a user has a registered account.
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            // Redirect to the password request page if no token is found in the session
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to prevent the URL from being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
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
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle'), // Corrected: Use $e->getReason() directly
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A ResetPasswordRequest object should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode(hash) the plain password, and set it.
            /** @var Compte $user */
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->addFlash('success', $translator->trans('reset_password.success', [], 'app'));

            return $this->redirectToRoute('app_home'); // Rediriger vers la page d'accueil ou de connexion
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator, LoggerInterface $logger): RedirectResponse
    {
        $user = $this->entityManager->getRepository(Compte::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }https://github.com/Erico-Labare/cc_symfony_AIDB/pull/11/conflict?name=src%252FController%252Fadmin%252FChambreController.php&ancestor_oid=4d16ae145b0e3eb6340a1309bfaa57bc18d44445&base_oid=c0054b74702645d08e4d3874364aeee4f2690881&head_oid=9328c5c10b196288d0268e7c638d009ee2cd7380

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

        // Store the token object in session for retrieval in checkEmail()
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
