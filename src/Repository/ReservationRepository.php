<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * Recherche une réservation par identifiant
     */
    // public function findReservationById(int $id): ?Reservation
    // {
    //     return $this->createQueryBuilder('r')
    //         ->andWhere('r.id = :id')
    //         ->setParameter('id', $id)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }

    /**
     * Retourne les réservations comprises entre deux dates
     */
    // public function findBetweenDates(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    // {
    //     return $this->createQueryBuilder('r')
    //         ->andWhere('r.dateDebut <= :dateFin')
    //         ->andWhere('r.dateFin >= :dateDebut')
    //         ->setParameter('dateDebut', $dateDebut)
    //         ->setParameter('dateFin', $dateFin)
    //         ->orderBy('r.dateDebut', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}