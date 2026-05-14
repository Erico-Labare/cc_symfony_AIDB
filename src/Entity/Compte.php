<?php

namespace App\Entity;


use App\Repository\CompteRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CompteRepository::class)]

#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email est déjà utilisé.'
)]
class Compte implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Clé primaire auto-générée.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Définit les permissions de l'utilisateur.
     *
     * Exemples :
     * - ROLE_USER
     * - ROLE_ADMIN
     */

    #[ORM\Column(length: 50)]
    private ?string $role = 'ROLE_USER';

    /**
     * Sert d'identifiant de connexion.
     *
     * Contraintes :
     * - obligatoire
     * - format email valide
     * - unique en base de données
     */

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Le mot de passe est stocké hashé via le composant
     * Security de Symfony.
     */

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * Un compte peut posséder plusieurs réservations.
     *
     * Relation :
     * Compte 1 <> 0,n Reservation
     */

    /**
     * @var Collection<int, Reservation>
     */


    #[ORM\OneToMany(mappedBy: 'compte', targetEntity: Reservation::class)]

    private Collection $reservations;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * Constructeur
     */

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /*
    |--------------------------------------------------------------------------
    | GETTERS / SETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'identifiant du compte.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'email du compte.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'email du compte.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | SÉCURITÉ SYMFONY
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'identifiant utilisé pour la connexion.
     *
     * Symfony utilise cette méthode pour authentifier l'utilisateur.
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Retourne les rôles de l'utilisateur.
     *
     * Symfony attend obligatoirement un tableau.
     */
    public function getRoles(): array
    {
        /*
        | $this->role est une string.
        | Il faut donc retourner un tableau.
        */
        $roles = [$this->role];

        /*
        | Garantit qu'un utilisateur possède toujours ROLE_USER au minimum.
        */
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit le rôle du compte.
     */
    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Retourne le mot de passe (hashé).
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe hashé.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Permet d'effacer d'éventuelles données sensibles temporaires.
     * ex : Utile si l'on stocke temporairement un mot de passe en clair.
     */
    public function eraseCredentials(): void
    {
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne les réservations associées au compte.
     *
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODES DE GESTION DES RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Permet d'ajouter une réservation au compte.
     */
    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setCompte($this);
        }

        return $this;
    }

    /**
     * Permet de retirer une réservation du compte.
     */
    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getCompte() === $this) {
                $reservation->setCompte(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}