<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente l'entité Hotel.
 *
 * Cette classe définit la structure des données pour un hôtel, y compris
 * ses propriétés (nom, adresse, catégorie) et ses relations avec d'autres entités,
 * notamment les chambres. Elle inclut également des règles de validation
 * pour assurer l'intégrité des données.
 */
#[ORM\Entity(repositoryClass: HotelRepository::class)]
class Hotel
{
    /**
     * L'identifiant unique de l'hôtel.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le nom de l'hôtel.
     *
     * Doit être non vide et ne pas dépasser 50 caractères.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "Le nom de l'hôtel ne peut pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le nom de l'hôtel ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    /**
     * L'adresse physique de l'hôtel.
     *
     * Doit être non vide.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "L'adresse de l'hôtel ne peut pas être vide.")]
    #[ORM\Column(type: 'text')]
    private ?string $adresse = null;

    /**
     * La catégorie de l'hôtel, généralement représentée par des étoiles.
     *
     * Exemples : "*", "**", "***", "****", "*****".
     * Doit être non vide et ne pas dépasser 5 caractères.
     *
     * @var string|null
     */
    #[Assert\NotBlank(message: "La catégorie de l'hôtel ne peut pas être vide.")]
    #[Assert\Length(max: 5, maxMessage: "La catégorie de l'hôtel ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 5)]
    private ?string $categorie = null;

    /**
     * Collection des chambres associées à cet hôtel.
     *
     * Un hôtel peut avoir plusieurs chambres.
     *
     * @var Collection<int, Chambre>
     */
    #[ORM\OneToMany(mappedBy: 'hotel', targetEntity: Chambre::class)]
    private Collection $chambres;

    /**
     * Constructeur de la classe Hotel.
     *
     * Initialise la collection de chambres.
     */
    public function __construct()
    {
        $this->chambres = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant de l'hôtel.
     *
     * @return int|null L'identifiant de l'hôtel ou null si non persisté.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom de l'hôtel.
     *
     * @return string|null Le nom de l'hôtel.
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom de l'hôtel.
     *
     * @param string $nom Le nouveau nom de l'hôtel.
     * @return static
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Retourne l'adresse de l'hôtel.
     *
     * @return string|null L'adresse de l'hôtel.
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * Définit l'adresse de l'hôtel.
     *
     * @param string $adresse La nouvelle adresse de l'hôtel.
     * @return static
     */
    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Retourne la catégorie de l'hôtel.
     *
     * @return string|null La catégorie de l'hôtel.
     */
    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    /**
     * Définit la catégorie de l'hôtel.
     *
     * @param string $categorie La nouvelle catégorie de l'hôtel.
     * @return static
     */
    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Retourne la collection des chambres associées à cet hôtel.
     *
     * @return Collection<int, Chambre> La collection de chambres.
     */
    public function getChambres(): Collection
    {
        return $this->chambres;
    }

    /**
     * Ajoute une chambre à la collection de l'hôtel.
     *
     * Si la chambre n'est pas déjà associée à cet hôtel, elle est ajoutée
     * et la relation bidirectionnelle est établie.
     *
     * @param Chambre $chambre La chambre à ajouter.
     * @return static
     */
    public function addChambre(Chambre $chambre): static
    {
        if (!isset($this->chambres)) {
            $this->chambres = new ArrayCollection();
        }

        if (!$this->chambres->contains($chambre)) {
            $this->chambres->add($chambre);
            $chambre->setHotel($this);
        }

        return $this;
    }

    /**
     * Retire une chambre de la collection de l'hôtel.
     *
     * Si la chambre est retirée, la relation bidirectionnelle est rompue.
     *
     * @param Chambre $chambre La chambre à retirer.
     * @return static
     */
    public function removeChambre(Chambre $chambre): static
    {
        if ($this->chambres->removeElement($chambre)) {
            // set the owning side to null (unless already changed)
            if ($chambre->getHotel() === $this) {
                $chambre->setHotel(null);
            }
        }

        return $this;
    }
}
