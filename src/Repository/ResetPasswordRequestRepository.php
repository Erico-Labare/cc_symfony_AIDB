<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use App\Entity\Compte; // Assuming your User entity is App\Entity\Compte

/**
 * Repository de gestion des demandes de réinitialisation de mot de passe
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->persist($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->remove($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * @return string The user identifier (e.g. email address)
     */
    public function getUserIdentifier(object $user): string
    {
        // Assuming your User entity (Compte) has a getEmail() method
        if ($user instanceof Compte) {
            return $user->getEmail();
        }

        throw new \InvalidArgumentException('User object must be an instance of App\Entity\Compte.');
    }

    /**
     * @return \DateTimeInterface|null The most recent non-expired reset password request date for the user, or null if no such request exists
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
     * Remove all expired reset password requests.
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
