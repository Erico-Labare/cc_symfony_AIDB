<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;

use Doctrine\ORM\Mapping as ORM;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    /**
     * Identifiant unique de la demande de réinitialisation.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Compte associé à la demande de réinitialisation.
     *
     * Relation :
     * Plusieurs demandes peuvent être générées pour un compte.
     *
     * ResetPasswordRequest N <> 1 Compte
     */

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Compte $user = null;

    /**
     * Constructeur obligatoire pour le bundle SymfonyCasts ResetPassword.
     *
     * @param object $user
     * @param \DateTimeInterface $expiresAt
     * @param string $selector
     * @param string $hashedToken
     */
    public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;

        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /*
    |--------------------------------------------------------------------------
    | GETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'identifiant de la demande.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur associé à la demande.
     */
    public function getUser(): object
    {
        return $this->user;
    }

    // Explicitly add methods from the trait to make them discoverable by PHPUnit
    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }
}
