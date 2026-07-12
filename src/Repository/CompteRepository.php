<?php

namespace App\Repository;

use App\Entity\Compte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * Récupère une liste paginée de comptes avec option de recherche.
     *
     * @param int $page La page actuelle
     * @param int $limit Le nombre d'éléments par page
     * @param string|null $search Le terme de recherche (email ou rôle)
     * @return Paginator
     */
    public function paginateComptes(int $page, int $limit, ?string $search = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->orderBy('c.email', 'ASC');

        if ($search) {
            $queryBuilder->andWhere('c.email LIKE :search OR c.role LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, false); // Ajout de 'false' ici
    }
}
