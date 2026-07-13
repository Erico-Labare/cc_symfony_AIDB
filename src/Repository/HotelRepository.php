<?php

namespace App\Repository;

use App\Entity\Hotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Dépôt pour l'entité Hotel.
 *
 * Ce dépôt fournit des méthodes pour interagir avec la base de données
 * spécifiquement pour l'entité Hotel, y compris la pagination et la recherche.
 *
 * @extends ServiceEntityRepository<Hotel>
 */
class HotelRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du HotelRepository.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hotel::class);
    }

    /**
     * Récupère une liste paginée d'hôtels avec une option de recherche.
     *
     * Permet de filtrer les hôtels par leur nom ou leur catégorie.
     *
     * @param int $page La page actuelle à récupérer (commence à 1).
     * @param int $limit Le nombre maximum d'hôtels par page.
     * @param string|null $search Le terme de recherche pour filtrer par nom ou catégorie.
     * @return Paginator Une instance de Paginator contenant les hôtels pour la page demandée.
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

        return new Paginator($query, false);
    }
}
