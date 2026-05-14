<?php

namespace App\Entity;

use App\Repository\HotelRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HotelRepository::class)]

class Hotel
{
    /**
     * Clé primaire auto-générée.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom de l'hôtel.
     *
     * Contraintes :
     * - obligatoire
     * - longueur max : 50 caractères
     */

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    /**
     * Adresse de l'hôtel.
     *
     * Contraintes :
     * - obligatoire
     */

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    private ?string $adresse = null;

    /**
     * Catégorie de l'hôtel.
     *
     * Exemples :
     * - *
     * - **
     * - ***
     * - ****
     * - *****
     *
     * Contraintes :
     * - obligatoire
     */

    #[Assert\NotBlank]
    #[Assert\Length(max: 5)]
    #[ORM\Column(length: 5)]
    private ?string $categorie = null;

    /**
     * Un hôtel possède plusieurs chambres.
     *
     * Relation :
     * Hotel 1 <> 0,n Chambre
     */

    /**
     * @var Collection<int, Chambre>
     */


    #[ORM\OneToMany(mappedBy: 'hotel', targetEntity: Chambre::class)]
    private Collection $chambres;

    /**
     * Constructeur
     */
    public function __construct()
    {
        // $this->chambres = new ArrayCollection();
    }

    /*
    |--------------------------------------------------------------------------
    | GETTERS / SETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'identifiant de l'hôtel.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom de l'hôtel.
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom de l'hôtel.
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Retourne l'adresse de l'hôtel.
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * Définit l'adresse de l'hôtel.
     */
    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Retourne la catégorie de l'hôtel.
     */
    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    /**
     * Définit la catégorie de l'hôtel.
     */
    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne les chambres associées à l'hôtel.
     *
     * @return Collection<int, Chambre>
     */

    public function getChambres(): Collection
    {
        return $this->chambres;
    }

    /**
     * Ajoute une chambre à l'hôtel.
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
     * Retire une chambre de l'hôtel.
     */
    public function removeChambre(Chambre $chambre): static
    {
        if ($this->chambres->removeElement($chambre)) {
            if ($chambre->getHotel() === $this) {
                $chambre->setHotel(null);
            }
        }

        return $this;
    }
}