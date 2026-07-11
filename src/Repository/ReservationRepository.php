<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Repository de gestion des réservations
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Récupère une liste paginée de réservations avec option de recherche.
     *
     * @param int $page La page actuelle
     * @param int $limit Le nombre d'éléments par page
     * @param string|null $search Le terme de recherche (ID de réservation, email du client, ID de chambre)
     * @return Paginator
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
            $queryBuilder->andWhere('CAST(r.id AS string) LIKE :search OR cl.email LIKE :search OR CAST(ch.id AS string) LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

    /**
     * Retourne les réservations comprises entre deux dates
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
