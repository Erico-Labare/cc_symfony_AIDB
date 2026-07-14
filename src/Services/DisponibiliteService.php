<?php

namespace App\Services;

use App\Entity\Chambre;
use App\Repository\ChambreRepository;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Exception\InvalidReservationDatesException;

/**
 * Service de gestion de la disponibilité des chambres.
 *
 * Cette classe fournit des méthodes pour vérifier la disponibilité des chambres
 * et trouver les chambres disponibles pour une période donnée dans un hôtel spécifique.
 */
class DisponibiliteService
{
    /**
     * Constructeur du service DisponibiliteService.
     *
     * @param ChambreRepository $chambreRepository Le dépôt des chambres pour accéder aux données des chambres.
     * @param ReservationRepository $reservationRepository Le dépôt des réservations pour vérifier les conflits.
     */
    public function __construct(
        private ChambreRepository $chambreRepository,
        private ReservationRepository $reservationRepository,
    ) {}

    /**
     * Trouve toutes les chambres disponibles pour un hôtel donné (ou tous les hôtels) et une période spécifique.
     *
     * Cette méthode filtre les chambres qui ne sont pas déjà réservées
     * pour la période spécifiée.
     *
     * @param \DateTimeInterface $dateDebut La date et l'heure de début de la période de recherche.
     * @param \DateTimeInterface $dateFin La date et l'heure de fin de la période de recherche.
     * @param int|null $hotelId L'identifiant de l'hôtel dans lequel rechercher les chambres. Si null, recherche dans tous les hôtels.
     *
     * @return Collection<int, Chambre> Une collection de chambres disponibles.
     *
     * @throws InvalidReservationDatesException Si la date de fin est antérieure ou égale à la date de début.
     */
    public function findAvailableRooms(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?int $hotelId = null): Collection
    {
        if ($dateFin <= $dateDebut) {
            throw new InvalidReservationDatesException(
                'La date de fin ne peut pas être antérieure à la date de début.',
                'reservation.error.invalid_dates_order'
            );
        }

        // Récupère toutes les chambres de l'hôtel spécifié ou de tous les hôtels
        if ($hotelId !== null) {
            $allRooms = $this->chambreRepository->findBy(['hotel' => $hotelId]);
        } else {
            $allRooms = $this->chambreRepository->findAll(); // Fetch all rooms if no hotelId is provided
        }

        // Récupère toutes les réservations qui chevauchent la période donnée
        $reservedRooms = $this->reservationRepository->findBetweenDates($dateDebut, $dateFin);

        // Extrait les IDs des chambres réservées
        $reservedRoomIds = array_map(fn($res) => $res->getChambre()->getId(), $reservedRooms);

        // Filtre les chambres pour ne garder que celles qui ne sont pas réservées
        $availableRooms = array_filter(
            $allRooms,
            fn($room) => !in_array($room->getId(), $reservedRoomIds)
        );

        return new ArrayCollection($availableRooms);
    }

    /**
     * Vérifie si une chambre spécifique est disponible pour une période donnée.
     *
     * Cette méthode utilise `findAvailableRooms` pour déterminer si la chambre
     * fait partie des chambres disponibles pour l'hôtel et la période spécifiés.
     *
     * @param Chambre $chambre La chambre dont la disponibilité doit être vérifiée.
     * @param \DateTimeInterface $dateDebut La date et l'heure de début de la période.
     * @param \DateTimeInterface $dateFin La date et l'heure de fin de la période.
     *
     * @return bool Vrai si la chambre est disponible, faux sinon.
     *
     * @throws InvalidReservationDatesException Si les dates sont invalides (délégué à findAvailableRooms).
     */
    public function isRoomAvailable(Chambre $chambre, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): bool
    {
        // Cette méthode peut lever InvalidReservationDatesException via findAvailableRooms
        return $this->findAvailableRooms($dateDebut, $dateFin, $chambre->getHotel()->getId())->contains($chambre);
    }
}
