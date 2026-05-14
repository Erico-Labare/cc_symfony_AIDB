<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository de gestion des clients
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Recherche des clients par nom
     */
    // public function findByNom(string $nom): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.nom LIKE :nom')
    //         ->setParameter('nom', '%' . $nom . '%')
    //         ->orderBy('c.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    /**
     * Recherche un client par email
     */
    // public function findByEmail(string $email): ?Client
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.email = :email')
    //         ->setParameter('email', $email)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }
}