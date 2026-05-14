<?php

namespace App\Repository;

use App\Entity\Chambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository de gestion des chambres
 */
class ChambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chambre::class);
    }

    /**
     * Recherche des chambres par type
     */
    // public function findByType(string $type): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.type = :type')
    //         ->setParameter('type', $type)
    //         ->orderBy('c.id', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    /**
     * Recherche des chambres par étage
     */
    // public function findByEtage(int $etage): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.etage = :etage')
    //         ->setParameter('etage', $etage)
    //         ->orderBy('c.id', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}