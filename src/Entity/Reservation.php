<?php

namespace App\Entity;

use App\Repository\ReservationRepository;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]

class Reservation
{
    /**
     * Clé primaire auto-générée.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Date de début de la réservation.
     *
     * Contraintes :
     * - obligatoire
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDebut = null;

    /**
     * Date de fin de la réservation.
     *
     * Contraintes :
     * - obligatoire
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateFin = null;

    /**
     * Commentaire / demande spéciale du client.
     *
     * Exemples :
     * - lit bébé
     * - chambre non-fumeur
     * - étage bas
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    /**
     * Relation :
     * Plusieurs réservations peuvent être créées par un compte.
     *
     * Reservation N <> 1 Compte
     */

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Compte $compte = null;

    /**
     * Relation :
     * Plusieurs réservations peuvent appartenir à un client.
     *
     * Reservation N <> 1 Client
     */


    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    /**
     * Relation :
     * Plusieurs réservations peuvent concerner une chambre.
     *
     * Reservation N <> 1 Chambre
     */

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chambre $chambre = null;

    /*
    |--------------------------------------------------------------------------
    | GETTERS / SETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'identifiant de la réservation.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne la date de début.
     */
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    /**
     * Définit la date de début.
     */
    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Retourne la date de fin.
     */
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    /**
     * Définit la date de fin.
     */
    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Retourne le commentaire de la réservation.
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    /**
     * Définit le commentaire de la réservation.
     */
    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne le compte qui a créé la réservation.
     */

    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    public function setCompte(?Compte $compte): static
    {
        $this->compte = $compte;
        return $this;
    }


    /**
     * Retourne le client auquel appartient la réservation.
     */

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }


    /**
     * Retourne la chambre concernée par la réservation.
     */

    /**
     * Retourne la chambre concernée par la réservation.
     */

    public function getChambre(): ?Chambre
    {
        return $this->chambre;
    }

    public function setChambre(?Chambre $chambre): static
    {
        $this->chambre = $chambre;
        return $this;
    }

}