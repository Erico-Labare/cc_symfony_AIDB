<?php

namespace App\Repository;

use App\Entity\Chambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * Récupère une liste paginée de chambres avec option de recherche.
     *
     * @param int $page La page actuelle
     * @param int $limit Le nombre d'éléments par page
     * @param string|null $search Le terme de recherche (type ou étage)
     * @return Paginator
     */
    public function paginateChambres(int $page, int $limit, ?string $search = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.hotel', 'h')
            ->addSelect('h')
            ->orderBy('c.id', 'ASC');

        if ($search) {
            // Convertir 'etage' en string pour la recherche LIKE
            $queryBuilder->andWhere('c.type LIKE :search OR CONCAT(c.etage, \'\') LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $query = $queryBuilder->getQuery();
        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, false); // Ajout de 'false' ici
    }
}
