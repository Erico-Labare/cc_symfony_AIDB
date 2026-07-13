<?php

namespace App\Services;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Reservation;
use App\Exception\InvalidReservationDatesException;
use App\Exception\RoomUnavailableException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de gestion des réservations.
 *
 * Cette classe encapsule la logique métier liée à la création et à la gestion
 * des réservations, y compris la vérification de la disponibilité des chambres
 * et la validation des dates.
 */
class ReservationService
{
    /**
     * Constructeur du service ReservationService.
     *
     * @param DisponibiliteService $disponibiliteService Le service de vérification de disponibilité des chambres.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine pour la persistance.
     */
    public function __construct(
        private DisponibiliteService $disponibiliteService,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Crée une nouvelle réservation après avoir vérifié la validité des dates
     * et la disponibilité de la chambre.
     *
     * @param Chambre $chambre La chambre que le client souhaite réserver.
     * @param Client $client Le client pour lequel la réservation est effectuée.
     * @param Compte $compte Le compte utilisateur qui initie la réservation.
     * @param \DateTimeInterface $dateDebut La date et l'heure de début de la réservation.
     * @param \DateTimeInterface $dateFin La date et l'heure de fin de la réservation.
     * @param string|null $commentaire Un commentaire ou une demande spéciale pour la réservation (optionnel).
     *
     * @return Reservation L'objet Reservation nouvellement créé et persisté.
     *
     * @throws InvalidReservationDatesException Si la date de fin est antérieure ou égale à la date de début.
     * @throws RoomUnavailableException Si la chambre n'est pas disponible pour la période demandée.
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
            throw new InvalidReservationDatesException(
                'La date de fin doit être après la date de début.',
                'reservation.error.invalid_dates_order'
            );
        }

        if (!$this->disponibiliteService->isRoomAvailable($chambre, $dateDebut, $dateFin)) {
            throw new RoomUnavailableException(
                'La chambre n\'est pas disponible pour cette période.',
                'reservation.error.room_unavailable'
            );
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
