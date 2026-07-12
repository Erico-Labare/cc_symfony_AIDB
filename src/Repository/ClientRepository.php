<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Compte;


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

        return new Paginator($query, false); // Ajout de 'false' ici
    }
        /**
     * Retourne tous les clients déjà associés à un compte via ses réservations.
     */
    public function findByCompte(Compte $compte): array
    {
        return $this->createQueryBuilder('c')
            ->distinct()
            ->innerJoin('c.reservations', 'r')
            ->where('r.compte = :compte')
            ->setParameter('compte', $compte)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
        /**
     * Recherche un client appartenant déjà au compte connecté
     * grâce à son adresse email.
     */
    public function findClientForCompteByEmail(Compte $compte, string $email): ?Client
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.reservations', 'r')
            ->where('r.compte = :compte')
            ->andWhere('c.email = :email')
            ->setParameter('compte', $compte)
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
