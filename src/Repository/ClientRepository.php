<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Compte;

/**
 * Dépôt pour l'entité Client.
 *
 * Ce dépôt fournit des méthodes pour interagir avec la base de données
 * spécifiquement pour l'entité Client, y compris la pagination, la recherche,
 * et la récupération de clients associés à un compte utilisateur.
 *
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du ClientRepository.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Récupère une liste paginée de clients avec une option de recherche.
     *
     * Permet de filtrer les clients par leur nom ou leur adresse email.
     *
     * @param int $page La page actuelle à récupérer (commence à 1).
     * @param int $limit Le nombre maximum d'éléments par page.
     * @param string|null $search Le terme de recherche pour filtrer par nom ou email.
     * @return Paginator Une instance de Paginator contenant les clients pour la page demandée.
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

        return new Paginator($query, false);
    }

    /**
     * Retourne tous les clients qui ont au moins une réservation associée à un compte donné.
     *
     * @param Compte $compte Le compte utilisateur pour lequel récupérer les clients.
     * @return Client[] Un tableau de clients distincts associés au compte.
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
     * Recherche un client spécifique associé à un compte donné par son adresse email.
     *
     * Utile pour vérifier si un client avec une certaine adresse email existe déjà
     * et est lié à l'utilisateur connecté via une réservation.
     *
     * @param Compte $compte Le compte utilisateur.
     * @param string $email L'adresse email du client à rechercher.
     * @return Client|null Le client trouvé ou null si aucun client correspondant n'est trouvé.
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
