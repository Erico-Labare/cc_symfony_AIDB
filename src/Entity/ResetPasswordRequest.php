<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * Représente l'entité ResetPasswordRequest.
 *
 * Cette classe est utilisée par le bundle SymfonyCasts/ResetPasswordBundle
 * pour gérer les demandes de réinitialisation de mot de passe. Elle stocke
 * les informations nécessaires pour valider et traiter une demande de
 * réinitialisation, y compris l'utilisateur concerné, la date d'expiration
 * et les jetons de sécurité.
 */
#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    /**
     * L'identifiant unique de la demande de réinitialisation de mot de passe.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le compte utilisateur associé à cette demande de réinitialisation.
     *
     * Une demande de réinitialisation est toujours liée à un utilisateur spécifique.
     *
     * @var Compte|null
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Compte $user = null;

    /**
     * Constructeur de la demande de réinitialisation de mot de passe.
     *
     * Initialise une nouvelle demande avec l'utilisateur, la date d'expiration,
     * le sélecteur et le jeton haché.
     *
     * @param object $user L'objet utilisateur (doit être une instance de Compte).
     * @param \DateTimeInterface $expiresAt La date et l'heure d'expiration de la demande.
     * @param string $selector Le sélecteur du jeton.
     * @param string $hashedToken Le jeton haché.
     */
    public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * Retourne l'identifiant unique de la demande de réinitialisation.
     *
     * @return int|null L'identifiant de la demande ou null si non persistée.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur associé à cette demande de réinitialisation.
     *
     * @return object L'objet utilisateur (instance de Compte).
     */
    public function getUser(): object
    {
        return $this->user;
    }

    /**
     * Retourne le sélecteur du jeton de réinitialisation.
     *
     * Cette méthode est fournie par le trait ResetPasswordRequestTrait.
     *
     * @return string Le sélecteur.
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * Retourne le jeton haché de réinitialisation.
     *
     * Cette méthode est fournie par le trait ResetPasswordRequestTrait.
     *
     * @return string Le jeton haché.
     */
    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }
}
