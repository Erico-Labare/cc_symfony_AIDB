<?php

namespace App\Repository;

use App\Entity\Hotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * Récupère une liste paginée d'hôtels avec option de recherche.
     *
     * @param int $page La page actuelle
     * @param int $limit Le nombre d'éléments par page
     * @param string|null $search Le terme de recherche (nom ou catégorie)
     * @return Paginator
     */
    public function paginateHotels(int $page, int $limit, ?string $search = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('h')
            ->orderBy('h.nom', 'ASC');

        if ($search) {
            $queryBuilder->andWhere('h.nom LIKE :search OR h.categorie LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }
}
