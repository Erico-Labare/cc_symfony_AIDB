<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Dépôt pour l'entité Reservation.
 *
 * Ce dépôt fournit des méthodes pour interagir avec la base de données
 * spécifiquement pour l'entité Reservation, y compris la pagination, la recherche,
 * et la récupération de réservations qui chevauchent une période donnée.
 *
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du ReservationRepository.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Récupère une liste paginée de réservations avec une option de recherche.
     *
     * Permet de filtrer les réservations par leur ID, l'email du client ou l'ID de la chambre.
     * Inclut également les entités Client et Chambre associées.
     *
     * @param int $page La page actuelle à récupérer (commence à 1).
     * @param int $limit Le nombre maximum d'éléments par page.
     * @param string|null $search Le terme de recherche pour filtrer.
     * @return Paginator Une instance de Paginator contenant les réservations pour la page demandée.
     */
    public function paginateReservations(int $page, int $limit, ?string $search = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->leftJoin('r.client', 'cl')
            ->addSelect('cl')
            ->leftJoin('r.chambre', 'ch')
            ->addSelect('ch')
            ->orderBy('r.dateDebut', 'DESC');

        if ($search) {
            // Recherche par ID de réservation, email du client ou ID de chambre
            // Utilisation de CONCAT pour convertir les IDs numériques en string pour la recherche LIKE
            $queryBuilder->andWhere('CONCAT(r.id, \'\') LIKE :search OR cl.email LIKE :search OR CONCAT(ch.id, \'\') LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, false);
    }

    /**
     * Retourne toutes les réservations qui chevauchent une période donnée.
     *
     * Une réservation chevauche la période si :
     * - Sa date de début est avant ou égale à la date de fin de la période.
     * - Sa date de fin est après ou égale à la date de début de la période.
     *
     * @param \DateTimeInterface $dateDebut La date de début de la période à vérifier.
     * @param \DateTimeInterface $dateFin La date de fin de la période à vérifier.
     * @return Reservation[] Un tableau des réservations qui chevauchent la période.
     */
    public function findBetweenDates(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.dateDebut <= :dateFin')
            ->andWhere('r.dateFin >= :dateDebut')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('r.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
