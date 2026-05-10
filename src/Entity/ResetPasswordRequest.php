<?php

namespace App\Entity;

// A REACTIVER PLUS TARD
// use App\Repository\ResetPasswordRequestRepository;

use Doctrine\ORM\Mapping as ORM;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

// A REACTIVER PLUS TARD
// #[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]

#[ORM\Entity]
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
}