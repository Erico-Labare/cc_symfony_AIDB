<?php

namespace App\Repository;

use App\Entity\Compte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Dépôt pour l'entité Compte.
 *
 * Ce dépôt fournit des méthodes pour interagir avec la base de données
 * spécifiquement pour l'entité Compte, y compris la pagination et la recherche.
 *
 * @extends ServiceEntityRepository<Compte>
 */
class CompteRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du CompteRepository.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compte::class);
    }

    /**
     * Récupère une liste paginée de comptes utilisateurs avec une option de recherche.
     *
     * Permet de filtrer les comptes par leur adresse email ou leur rôle.
     *
     * @param int $page La page actuelle à récupérer (commence à 1).
     * @param int $limit Le nombre maximum d'éléments par page.
     * @param string|null $search Le terme de recherche pour filtrer par email ou rôle.
     * @return Paginator Une instance de Paginator contenant les comptes pour la page demandée.
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

        return new Paginator($query, false);
    }
}
