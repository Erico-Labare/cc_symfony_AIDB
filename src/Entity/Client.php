<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente l'entité Client.
 *
 * Cette classe définit la structure des données pour un client, incluant
 * ses informations personnelles (nom, adresse, email, téléphone) et
 * ses relations avec les réservations. Elle intègre des règles de validation
 * pour garantir la conformité des données.
 */
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    /**
     * L'identifiant unique du client.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le nom complet du client.
     *
     * Doit être non vide et ne pas dépasser 120 caractères.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "Le nom du client ne peut pas être vide.")]
    #[Assert\Length(max: 120, maxMessage: "Le nom du client ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 120)]
    private ?string $nom = null;

    /**
     * L'adresse postale du client.
     *
     * Doit être non vide.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "L'adresse du client ne peut pas être vide.")]
    #[ORM\Column(type: 'text')]
    private ?string $adresse = null;

    /**
     * L'adresse email du client.
     *
     * Doit être non vide et avoir un format d'email valide.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "L'adresse email du client ne peut pas être vide.")]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas une adresse email valide.")]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * Le numéro de téléphone du client.
     *
     * Doit être non vide et ne pas dépasser 50 caractères.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "Le numéro de téléphone du client ne peut pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 50)]
    private ?string $telephone = null;

    /**
     * Collection des réservations effectuées par ce client.
     *
     * Un client peut avoir plusieurs réservations.
     *
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Reservation::class)]
    private Collection $reservations;

    /**
     * Constructeur de la classe Client.
     *
     * Initialise la collection de réservations.
     */
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant du client.
     *
     * @return int|null L'identifiant du client ou null si non persisté.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom du client.
     *
     * @return string|null Le nom du client.
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom du client.
     *
     * @param string $nom Le nouveau nom du client.
     * @return static
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Retourne l'adresse du client.
     *
     * @return string|null L'adresse du client.
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * Définit l'adresse du client.
     *
     * @param string $adresse La nouvelle adresse du client.
     * @return static
     */
    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Retourne l'email du client.
     *
     * @return string|null L'email du client.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'email du client.
     *
     * @param string $email La nouvelle adresse email du client.
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne le numéro de téléphone du client.
     *
     * @return string|null Le numéro de téléphone du client.
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * Définit le numéro de téléphone du client.
     *
     * @param string $telephone Le nouveau numéro de téléphone du client.
     * @return static
     */
    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Retourne la collection des réservations associées à ce client.
     *
     * @return Collection<int, Reservation> La collection de réservations.
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    /**
     * Ajoute une réservation à la collection du client.
     *
     * Si la réservation n'est pas déjà associée à ce client, elle est ajoutée
     * et la relation bidirectionnelle est établie.
     *
     * @param Reservation $reservation La réservation à ajouter.
     * @return static
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
     * Retire une réservation de la collection du client.
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
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }
}
