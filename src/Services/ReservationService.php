<?php

namespace App\Services;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    public function __construct(
        private DisponibiliteService $disponibiliteService,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Crée une nouvelle réservation après vérification de disponibilité.
     *
     * @param Chambre $chambre Chambre à réserver
     * @param Client $client Client qui réserve
     * @param Compte $compte Compte (utilisateur) qui crée la réservation
     * @param \DateTimeInterface $dateDebut Date de début
     * @param \DateTimeInterface $dateFin Date de fin
     * @param ?string $commentaire Commentaire optionnel
     *
     * @return Reservation La réservation créée
     *
     * @throws \InvalidArgumentException Si la chambre n'est pas disponible
     */
    public function createReservation(
        Chambre $chambre,
        Client $client,
        Compte $compte,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin,
        ?string $commentaire = null,
    ): Reservation {
        if ($dateFin <= $dateDebut) {
            throw new \InvalidArgumentException('La date de fin doit être après la date de début.');
        }

        if (!$this->disponibiliteService->isRoomAvailable($chambre, $dateDebut, $dateFin)) {
            throw new \InvalidArgumentException('La chambre n\'est pas disponible pour cette période.');
        }

        $reservation = new Reservation();
        $reservation->setChambre($chambre);
        $reservation->setClient($client);
        $reservation->setCompte($compte);
        $reservation->setDateDebut($dateDebut);
        $reservation->setDateFin($dateFin);
        $reservation->setCommentaire($commentaire);

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $reservation;
    }
}
