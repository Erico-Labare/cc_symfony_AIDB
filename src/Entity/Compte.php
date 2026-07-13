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

/**
 * Représente l'entité Compte.
 *
 * Cette classe définit la structure des données pour un compte utilisateur,
 * incluant ses informations d'authentification (email, mot de passe, rôles),
 * son statut de vérification et ses relations avec les réservations.
 * Elle implémente les interfaces UserInterface et PasswordAuthenticatedUserInterface
 * pour l'intégration avec le système de sécurité de Symfony.
 *
 * La contrainte UniqueEntity assure que l'adresse email est unique en base de données.
 */
#[ORM\Entity(repositoryClass: CompteRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email est déjà utilisé.'
)]
class Compte implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * L'identifiant unique du compte.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le rôle principal de l'utilisateur.
     *
     * Par défaut, 'ROLE_USER'. Peut être 'ROLE_ADMIN' pour les administrateurs.
     *
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private ?string $role = 'ROLE_USER';

    /**
     * L'adresse email du compte, utilisée comme identifiant de connexion.
     *
     * Doit être non vide, avoir un format d'email valide et être unique.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "L'adresse email ne peut pas être vide.")]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas une adresse email valide.")]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Le mot de passe haché de l'utilisateur.
     *
     * Stocké de manière sécurisée via le composant Security de Symfony.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * Collection des réservations associées à ce compte.
     *
     * Un compte peut avoir plusieurs réservations.
     *
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(mappedBy: 'compte', targetEntity: Reservation::class)]
    private Collection $reservations;

    /**
     * Indique si l'adresse email du compte a été vérifiée.
     *
     * @var bool
     */
    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * Constructeur de la classe Compte.
     *
     * Initialise la collection de réservations.
     */
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant unique du compte.
     *
     * @return int|null L'identifiant du compte ou null si non persisté.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'adresse email du compte.
     *
     * @return string|null L'adresse email du compte.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse email du compte.
     *
     * @param string $email La nouvelle adresse email du compte.
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne l'identifiant de l'utilisateur pour l'authentification.
     *
     * Cette méthode est requise par UserInterface et retourne l'email
     * qui sert d'identifiant unique pour la connexion.
     *
     * @return string L'identifiant de l'utilisateur.
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Retourne les rôles attribués à l'utilisateur.
     *
     * Cette méthode est requise par UserInterface. Elle garantit que
     * l'utilisateur possède au minimum le rôle 'ROLE_USER'.
     *
     * @return array<string> Un tableau de chaînes de caractères représentant les rôles.
     */
    public function getRoles(): array
    {
        $roles = [$this->role];
        // Garantit qu'un utilisateur possède toujours ROLE_USER au minimum.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit le rôle principal du compte.
     *
     * @param string $role Le nouveau rôle principal (ex: 'ROLE_USER', 'ROLE_ADMIN').
     * @return static
     */
    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Retourne le rôle principal du compte.
     *
     * @return string|null Le rôle principal du compte.
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * Retourne le mot de passe haché de l'utilisateur.
     *
     * Cette méthode est requise par PasswordAuthenticatedUserInterface.
     *
     * @return string|null Le mot de passe haché.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe haché de l'utilisateur.
     *
     * @param string $password Le mot de passe haché.
     * @return static
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Efface les données sensibles temporaires de l'utilisateur.
     *
     * Cette méthode est requise par UserInterface. Elle est généralement
     * utilisée pour nettoyer des informations comme un mot de passe en clair
     * après l'authentification.
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Retourne la collection des réservations associées à ce compte.
     *
     * @return Collection<int, Reservation> La collection de réservations.
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    /**
     * Ajoute une réservation à la collection du compte.
     *
     * Si la réservation n'est pas déjà associée à ce compte, elle est ajoutée
     * et la relation bidirectionnelle est établie.
     *
     * @param Reservation $reservation La réservation à ajouter.
     * @return static
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
     * Retire une réservation de la collection du compte.
     *
     * Si la réservation est retirée, la relation bidirectionnelle est rompue.
     *
     * @param Reservation $reservation La réservation à retirer.
     * @return static
     */
    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCompte() === $this) {
                $reservation->setCompte(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si l'adresse email du compte a été vérifiée.
     *
     * @return bool Vrai si l'email est vérifié, faux sinon.
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Définit le statut de vérification de l'adresse email du compte.
     *
     * @param bool $isVerified Le nouveau statut de vérification.
     * @return static
     */
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
