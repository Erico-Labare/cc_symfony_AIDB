<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * Récupère une liste paginée de clients avec option de recherche.
     *
     * @param int $page La page actuelle
     * @param int $limit Le nombre d'éléments par page
     * @param string|null $search Le terme de recherche (nom ou email)
     * @return Paginator
     */
    public function paginateClients(int $page, int $limit, ?string $search = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC');

        if ($search) {
            $queryBuilder->andWhere('c.nom LIKE :search OR c.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }
}
