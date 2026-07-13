<?php

namespace App\Repository;

use App\Entity\Chambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Dépôt pour l'entité Chambre.
 *
 * Ce dépôt fournit des méthodes pour interagir avec la base de données
 * spécifiquement pour l'entité Chambre, y compris la pagination et la recherche.
 *
 * @extends ServiceEntityRepository<Chambre>
 */
class ChambreRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du ChambreRepository.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chambre::class);
    }

    /**
     * Récupère une liste paginée de chambres avec une option de recherche.
     *
     * Permet de filtrer les chambres par leur type ou leur étage.
     * Inclut également l'hôtel associé à chaque chambre.
     *
     * @param int $page La page actuelle à récupérer (commence à 1).
     * @param int $limit Le nombre maximum d'éléments par page.
     * @param string|null $search Le terme de recherche pour filtrer par type ou étage.
     * @return Paginator Une instance de Paginator contenant les chambres pour la page demandée.
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

        return new Paginator($query, false);
    }
}
