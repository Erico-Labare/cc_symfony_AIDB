<?php

namespace App\Repository;

use App\Entity\Hotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository de gestion des hôtels
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    /**
     * Recherche des hôtels par nom
     */
    // public function findByNom(string $nom): array
    // {
    //     return $this->createQueryBuilder('h')
    //         ->andWhere('h.nom LIKE :nom')
    //         ->setParameter('nom', '%' . $nom . '%')
    //         ->orderBy('h.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    /**
     * Recherche des hôtels par catégorie
     */
    // public function findByCategorie(string $categorie): array
    // {
    //     return $this->createQueryBuilder('h')
    //         ->andWhere('h.categorie = :categorie')
    //         ->setParameter('categorie', $categorie)
    //         ->orderBy('h.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}