<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use App\Entity\Compte;

/**
 * Dépôt pour l'entité ResetPasswordRequest.
 *
 * Ce dépôt implémente l'interface `ResetPasswordRequestRepositoryInterface`
 * de SymfonyCasts/ResetPasswordBundle, fournissant les méthodes nécessaires
 * pour gérer la persistance et la récupération des demandes de réinitialisation
 * de mot de passe.
 *
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    /**
     * Constructeur du ResetPasswordRequestRepository.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * Crée une nouvelle instance de ResetPasswordRequest.
     *
     * @param object $user L'objet utilisateur pour lequel la demande est créée.
     * @param \DateTimeInterface $expiresAt La date d'expiration de la demande.
     * @param string $selector Le sélecteur du jeton.
     * @param string $hashedToken Le jeton haché.
     * @return ResetPasswordRequestInterface La nouvelle demande de réinitialisation de mot de passe.
     */
    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    /**
     * Persiste une demande de réinitialisation de mot de passe en base de données.
     *
     * @param ResetPasswordRequestInterface $resetPasswordRequest La demande à persister.
     */
    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->persist($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve une demande de réinitialisation de mot de passe par son sélecteur.
     *
     * @param string $selector Le sélecteur de la demande à trouver.
     * @return ResetPasswordRequestInterface|null La demande trouvée ou null si aucune correspondance.
     */
    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    /**
     * Supprime une demande de réinitialisation de mot de passe de la base de données.
     *
     * @param ResetPasswordRequestInterface $resetPasswordRequest La demande à supprimer.
     */
    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->remove($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne l'identifiant de l'utilisateur (par exemple, l'adresse email).
     *
     * @param object $user L'objet utilisateur.
     * @return string L'identifiant de l'utilisateur.
     * @throws \InvalidArgumentException Si l'objet utilisateur n'est pas une instance de Compte.
     */
    public function getUserIdentifier(object $user): string
    {
        // Supposons que votre entité utilisateur (Compte) a une méthode getEmail()
        if ($user instanceof Compte) {
            return $user->getEmail();
        }

        throw new \InvalidArgumentException('User object must be an instance of App\Entity\Compte.');
    }

    /**
     * Retourne la date la plus récente d'une demande de réinitialisation de mot de passe
     * non expirée pour l'utilisateur, ou null si aucune demande n'existe.
     *
     * @param object $user L'objet utilisateur.
     * @return \DateTimeInterface|null La date d'expiration la plus récente ou null.
     */
    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        $request = $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('r.expiresAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $request ? $request->getExpiresAt() : null;
    }

    /**
     * Supprime toutes les demandes de réinitialisation de mot de passe expirées.
     *
     * @return int Le nombre de demandes supprimées.
     */
    public function removeExpiredResetPasswordRequests(): int
    {
        return $this->createQueryBuilder('r')
            ->delete()
            ->where('r.expiresAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
