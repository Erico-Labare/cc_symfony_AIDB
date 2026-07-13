<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente l'entité Chambre.
 *
 * Cette classe définit la structure des données pour une chambre d'hôtel,
 * incluant ses propriétés (étage, type, nombre de lits) et ses relations
 * avec l'hôtel auquel elle appartient et les réservations qui la concernent.
 * Elle intègre des règles de validation pour garantir la conformité des données.
 */
#[ORM\Entity(repositoryClass: ChambreRepository::class)]
class Chambre
{
    /**
     * L'identifiant unique de la chambre.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * L'étage où se situe la chambre.
     *
     * Doit être non vide.
     *
     * @var int|null
     */
    #[Assert\NotBlank(message: "L'étage de la chambre ne peut pas être vide.")]
    #[ORM\Column]
    private ?int $etage = null;

    /**
     * Le type de la chambre (ex: "single", "double", "suite").
     *
     * Doit être non vide et ne pas dépasser 50 caractères.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "Le type de chambre ne peut pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le type de chambre ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 50)]
    private ?string $type = null;

    /**
     * Le nombre de lits disponibles dans la chambre.
     *
     * Doit être non vide.
     *
     * @var int|null
     */
    #[Assert\NotBlank(message: "Le nombre de lits ne peut pas être vide.")]
    #[ORM\Column]
    private ?int $nombreLit = null;

    /**
     * L'hôtel auquel cette chambre est associée.
     *
     * Chaque chambre doit être liée à un hôtel.
     *
     * @var Hotel|null
     */
    #[ORM\ManyToOne(inversedBy: 'chambres', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hotel $hotel = null;

    /**
     * Collection des réservations pour cette chambre.
     *
     * Une chambre peut être liée à plusieurs réservations.
     *
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(mappedBy: 'chambre', targetEntity: Reservation::class)]
    private Collection $reservations;

    /**
     * Constructeur de la classe Chambre.
     *
     * Initialise la collection de réservations.
     */
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant unique de la chambre.
     *
     * @return int|null L'identifiant de la chambre ou null si non persisté.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'étage de la chambre.
     *
     * @return int|null L'étage de la chambre.
     */
    public function getEtage(): ?int
    {
        return $this->etage;
    }

    /**
     * Définit l'étage de la chambre.
     *
     * @param int $etage Le nouvel étage de la chambre.
     * @return static
     */
    public function setEtage(int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    /**
     * Retourne le type de chambre.
     *
     * @return string|null Le type de chambre.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Définit le type de chambre.
     *
     * @param string $type Le nouveau type de chambre.
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Retourne le nombre de lits dans la chambre.
     *
     * @return int|null Le nombre de lits.
     */
    public function getNombreLit(): ?int
    {
        return $this->nombreLit;
    }

    /**
     * Définit le nombre de lits dans la chambre.
     *
     * @param int $nombreLit Le nouveau nombre de lits.
     * @return static
     */
    public function setNombreLit(int $nombreLit): static
    {
        $this->nombreLit = $nombreLit;

        return $this;
    }

    /**
     * Retourne l'hôtel associé à la chambre.
     *
     * @return Hotel|null L'hôtel associé.
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * Définit l'hôtel associé à la chambre.
     *
     * @param Hotel|null $hotel L'hôtel à associer.
     * @return static
     */
    public function setHotel(?Hotel $hotel): static
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Retourne la collection des réservations pour cette chambre.
     *
     * @return Collection<int, Reservation> La collection de réservations.
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    /**
     * Ajoute une réservation à la collection de la chambre.
     *
     * Si la réservation n'est pas déjà associée à cette chambre, elle est ajoutée
     * et la relation bidirectionnelle est établie.
     *
     * @param Reservation $reservation La réservation à ajouter.
     * @return static
     */
    public function addReservation(Reservation $reservation): static
    {
        if (!isset($this->reservations)) {
            $this->reservations = new ArrayCollection();
        }

        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setChambre($this);
        }

        return $this;
    }

    /**
     * Retire une réservation de la collection de la chambre.
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
            if ($reservation->getChambre() === $this) {
                $reservation->setChambre(null);
            }
        }

        return $this;
    }
}
