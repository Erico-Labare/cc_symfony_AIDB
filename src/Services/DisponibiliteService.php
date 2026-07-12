<?php

namespace App\Services;

use App\Entity\Chambre;
use App\Repository\ChambreRepository;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Exception\InvalidReservationDatesException;

class DisponibiliteService
{
    public function __construct(
        private ChambreRepository $chambreRepository,
        private ReservationRepository $reservationRepository,
    ) {}

    /**
     * Retourne les chambres disponibles pour une plage de dates donnée.
     *
     * @param \DateTimeInterface $dateDebut Date de début de la réservation
     * @param \DateTimeInterface $dateFin Date de fin de la réservation
     * @param int $hotelId Identifiant de l'hôtel
     *
     * @return Collection<int, Chambre> Chambres disponibles
     *
     * @throws InvalidReservationDatesException Si les dates sont invalides
     */
    public function findAvailableRooms(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, int $hotelId): Collection
    {
        if ($dateFin <= $dateDebut) {
            throw new InvalidReservationDatesException(
                'La date de fin doit être après la date de début.',
                'reservation.error.invalid_dates_order'
            );
        }

        $allRooms = $this->chambreRepository->findBy(['hotel' => $hotelId]);

        $reservedRooms = $this->reservationRepository->findBetweenDates($dateDebut, $dateFin);

        $reservedRoomIds = array_map(fn($res) => $res->getChambre()->getId(), $reservedRooms);

        $availableRooms = array_filter(
            $allRooms,
            fn($room) => !in_array($room->getId(), $reservedRoomIds)
        );

        return new ArrayCollection($availableRooms);
    }

    /**
     * Vérifie si une chambre est disponible pour une période donnée.
     *
     * @param Chambre $chambre Chambre à vérifier
     * @param \DateTimeInterface $dateDebut Date de début
     * @param \DateTimeInterface $dateFin Date de fin
     *
     * @return bool true si disponible, false sinon
     */
    public function isRoomAvailable(Chambre $chambre, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): bool
    {
        // Cette méthode peut lever InvalidReservationDatesException via findAvailableRooms
        return $this->findAvailableRooms($dateDebut, $dateFin, $chambre->getHotel()->getId())->contains($chambre);
    }
}
