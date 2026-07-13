<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Représente l'entité Reservation.
 *
 * Cette classe définit la structure des données pour une réservation,
 * incluant les dates de début et de fin, un commentaire optionnel,
 * et les relations avec le compte utilisateur qui a effectué la réservation,
 * le client pour qui la réservation est faite, et la chambre réservée.
 * Elle intègre des règles de validation pour garantir la conformité des données.
 */
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    /**
     * L'identifiant unique de la réservation.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * La date et l'heure de début de la réservation.
     *
     * Doit être non vide.
     *
     * @var \DateTimeInterface|null
     */
    #[Assert\NotBlank(message: "La date de début de la réservation ne peut pas être vide.")]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDebut = null;

    /**
     * La date et l'heure de fin de la réservation.
     *
     * Doit être non vide.
     *
     * @var \DateTimeInterface|null
     */
    #[Assert\NotBlank(message: "La date de fin de la réservation ne peut pas être vide.")]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateFin = null;

    /**
     * Un commentaire ou une demande spéciale concernant la réservation.
     *
     * Ce champ est optionnel.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    /**
     * Le compte utilisateur qui a effectué cette réservation.
     *
     * Chaque réservation doit être liée à un compte.
     *
     * @var Compte|null
     */
    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Compte $compte = null;

    /**
     * Le client pour qui cette réservation est faite.
     *
     * Chaque réservation doit être liée à un client.
     *
     * @var Client|null
     */
    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    /**
     * La chambre qui est réservée.
     *
     * Chaque réservation doit concerner une chambre.
     *
     * @var Chambre|null
     */
    #[ORM\ManyToOne(inversedBy: 'reservations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chambre $chambre = null;

    /**
     * Retourne l'identifiant unique de la réservation.
     *
     * @return int|null L'identifiant de la réservation ou null si non persisté.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne la date de début de la réservation.
     *
     * @return \DateTimeInterface|null La date de début.
     */
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    /**
     * Définit la date de début de la réservation.
     *
     * @param \DateTimeInterface $dateDebut La nouvelle date de début.
     * @return static
     */
    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Retourne la date de fin de la réservation.
     *
     * @return \DateTimeInterface|null La date de fin.
     */
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    /**
     * Définit la date de fin de la réservation.
     *
     * @param \DateTimeInterface $dateFin La nouvelle date de fin.
     * @return static
     */
    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Retourne le commentaire de la réservation.
     *
     * @return string|null Le commentaire.
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    /**
     * Définit le commentaire de la réservation.
     *
     * @param string|null $commentaire Le nouveau commentaire.
     * @return static
     */
    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Retourne le compte utilisateur associé à cette réservation.
     *
     * @return Compte|null Le compte utilisateur.
     */
    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    /**
     * Définit le compte utilisateur associé à cette réservation.
     *
     * @param Compte|null $compte Le compte utilisateur à associer.
     * @return static
     */
    public function setCompte(?Compte $compte): static
    {
        $this->compte = $compte;
        return $this;
    }

    /**
     * Retourne le client associé à cette réservation.
     *
     * @return Client|null Le client.
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * Définit le client associé à cette réservation.
     *
     * @param Client|null $client Le client à associer.
     * @return static
     */
    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Retourne la chambre associée à cette réservation.
     *
     * @return Chambre|null La chambre.
     */
    public function getChambre(): ?Chambre
    {
        return $this->chambre;
    }

    /**
     * Définit la chambre associée à cette réservation.
     *
     * @param Chambre|null $chambre La chambre à associer.
     * @return static
     */
    public function setChambre(?Chambre $chambre): static
    {
        $this->chambre = $chambre;
        return $this;
    }
}
