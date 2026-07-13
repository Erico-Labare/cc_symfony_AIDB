<?php

namespace App\Security;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Service de vérification d'email.
 *
 * Cette classe gère l'envoi d'emails de confirmation et la validation
 * des liens de vérification pour les comptes utilisateurs.
 */
class EmailVerifier
{
    /**
     * Constructeur du service EmailVerifier.
     *
     * @param VerifyEmailHelperInterface $verifyEmailHelper Aide à générer et valider les URLs de vérification.
     * @param MailerInterface $mailer Service d'envoi d'emails.
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine.
     */
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Envoie un email de confirmation à l'utilisateur.
     *
     * Génère une URL signée pour la vérification d'email et l'inclut dans le template
     * de l'email avant de l'envoyer.
     *
     * @param string $verifyEmailRouteName Le nom de la route utilisée pour la vérification d'email.
     * @param Compte $user L'entité utilisateur à vérifier.
     * @param TemplatedEmail $email L'objet email à envoyer, qui sera enrichi avec l'URL signée.
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, Compte $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            (string) $user->getEmail()
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * Gère la confirmation d'email à partir d'une requête.
     *
     * Valide la signature de l'URL de vérification, marque l'utilisateur comme vérifié
     * et persiste le changement en base de données.
     *
     * @param Request $request La requête HTTP contenant les paramètres de vérification.
     * @param Compte $user L'entité utilisateur dont l'email doit être vérifié.
     *
     * @throws VerifyEmailExceptionInterface Si la validation de l'email échoue (ex: lien expiré, signature invalide).
     */
    public function handleEmailConfirmation(Request $request, Compte $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), (string) $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
