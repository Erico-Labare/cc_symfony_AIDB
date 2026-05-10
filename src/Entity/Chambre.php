<?php

namespace App\Entity;

// A REACTIVER PLUS TARD
// use App\Repository\ChambreRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

// A REACTIVER PLUS TARD
// #[ORM\Entity(repositoryClass: ChambreRepository::class)]

class Chambre
{
    /**
     * Clé primaire auto-générée.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Numéro ou étage de la chambre.
     *
     * Contraintes :
     * - obligatoire
     */

    #[Assert\NotBlank]
    #[ORM\Column]
    private ?int $etage = null;

    /**
     * Type de chambre.
     *
     * Exemples :
     * - single
     * - double
     * - suite
     *
     * Contraintes :
     * - obligatoire
     */

    #[Assert\NotBlank]
    #[ORM\Column(length: 50)]
    private ?string $type = null;

    /**
     * Nombre de lits dans la chambre.
     *
     * Contraintes :
     * - obligatoire
     */

    #[Assert\NotBlank]
    #[ORM\Column]
    private ?int $nombreLit = null;

    /**
     * Relation :
     * Plusieurs chambres appartiennent à un hôtel.
     *
     * Chambre N <> 1 Hotel
     */

    // A REACTIVER PLUS TARD
    // #[ORM\ManyToOne(inversedBy: 'chambres')]
    // #[ORM\JoinColumn(nullable: false)]
    // private ?Hotel $hotel = null;

    /**
     * Une chambre peut être liée à plusieurs réservations.
     */

    /**
     * @var Collection<int, Reservation>
     */

    // A REACTIVER PLUS TARD
    // #[ORM\OneToMany(mappedBy: 'chambre', targetEntity: Reservation::class)]
    // private Collection $reservations;

    /**
     * Constructeur
     */
    public function __construct()
    {
        // $this->reservations = new ArrayCollection();
    }

    /*
    |--------------------------------------------------------------------------
    | GETTERS / SETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'identifiant de la chambre.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'étage de la chambre.
     */
    public function getEtage(): ?int
    {
        return $this->etage;
    }

    /**
     * Définit l'étage de la chambre.
     */
    public function setEtage(int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    /**
     * Retourne le type de chambre.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Définit le type de chambre.
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Retourne le nombre de lits.
     */
    public function getNombreLit(): ?int
    {
        return $this->nombreLit;
    }

    /**
     * Définit le nombre de lits.
     */
    public function setNombreLit(int $nombreLit): static
    {
        $this->nombreLit = $nombreLit;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // /**
    //  * Retourne l'hôtel associé à la chambre.
    //  */
    // public function getHotel(): ?Hotel
    // {
    //     return $this->hotel;
    // }

    // /**
    //  * Définit l'hôtel associé à la chambre.
    //  */
    // public function setHotel(?Hotel $hotel): static
    // {
    //     $this->hotel = $hotel;
    //     return $this;
    // }

    /*
    |--------------------------------------------------------------------------
    | RÉSERVATIONS
    |--------------------------------------------------------------------------
    */

    // /**
    //  * @return Collection<int, Reservation>
    //  */
    // public function getReservations(): Collection
    // {
    //     return $this->reservations;
    // }

    /**
     * Ajoute une réservation à la chambre.
     */
    // public function addReservation(Reservation $reservation): static
    // {
    //     if (!isset($this->reservations)) {
    //         $this->reservations = new ArrayCollection();
    //     }

    //     if (!$this->reservations->contains($reservation)) {
    //         $this->reservations->add($reservation);
    //         $reservation->setChambre($this);
    //     }

    //     return $this;
    // }

    /**
     * Retire une réservation de la chambre.
     */
    // public function removeReservation(Reservation $reservation): static
    // {
    //     if ($this->reservations->removeElement($reservation)) {
    //         if ($reservation->getChambre() === $this) {
    //             $reservation->setChambre(null);
    //         }
    //     }

    //     return $this;
    // }
}