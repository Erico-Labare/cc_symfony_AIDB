<?php

namespace App\Repository;

use App\Entity\Compte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository de gestion des comptes utilisateurs
 */
class CompteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compte::class);
    }

    /**
     * Recherche un compte par email
     */
    // public function findByEmail(string $email): ?Compte
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.email = :email')
    //         ->setParameter('email', $email)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }

    /**
     * Recherche des comptes par rôle
     */
    // public function findByRole(string $role): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->andWhere('c.role = :role')
    //         ->setParameter('role', $role)
    //         ->orderBy('c.id', 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }
}