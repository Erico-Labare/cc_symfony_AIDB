<?php

namespace App\Entity;

use App\Repository\ClientRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]

class Client
{
    /**
     * Clé primaire auto-générée.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom du client.
     *
     * Contraintes :
     * - obligatoire
     * - longueur maximale : 120 caractères
     */

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[ORM\Column(length: 120)]
    private ?string $nom = null;

    /**
     * Adresse postale du client.
     *
     * Contraintes :
     * - obligatoire
     */

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    private ?string $adresse = null;

    /**
     * Adresse email du client.
     *
     * Contraintes :
     * - obligatoire
     * - format email valide
     */

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * Numéro de téléphone du client.
     *
     * Contraintes :
     * - obligatoire
     * - longueur maximale : 50 caractères
     */

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50)]
    private ?string $telephone = null;

    /**
     * Un client peut posséder plusieurs réservations.
     *
     * Relation :
     * Client 1 <> 0,n Reservation
     */

    /**
     * @var Collection<int, Reservation>
     */


    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Reservation::class)]

    private Collection $reservations;

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
     * Retourne l'identifiant du client.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom du client.
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom du client.
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Retourne l'adresse du client.
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * Définit l'adresse du client.
     */
    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Retourne l'email du client.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'email du client.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne le numéro de téléphone du client.
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * Définit le numéro de téléphone du client.
     */
    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne les réservations associées au client.
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
     * Permet d'ajouter une réservation au client.
     */
    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setClient($this);
        }

        return $this;
    }

    /**
     * Permet de retirer une réservation du client.
     */
    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }
}
