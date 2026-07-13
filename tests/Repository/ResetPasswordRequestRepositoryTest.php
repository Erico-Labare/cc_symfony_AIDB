<?php

namespace App\Tests\Repository;

use App\Entity\Compte;
use App\Entity\ResetPasswordRequest;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

/**
 * Teste le ResetPasswordRequestRepository.
 *
 * Cette classe contient les tests unitaires pour vérifier le bon fonctionnement
 * des méthodes personnalisées du ResetPasswordRequestRepository, qui gère
 * les requêtes de réinitialisation de mot de passe.
 */
class ResetPasswordRequestRepositoryTest extends TestCase
{
    private $entityManager;
    private $managerRegistry;
    private $repository;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Crée des mocks pour l'EntityManager et le ManagerRegistry, puis initialise
     * une instance mockée du ResetPasswordRequestRepository.
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->managerRegistry->method('getManagerForClass')
            ->willReturn($this->entityManager);

        // This mock is needed for the ServiceEntityRepository constructor
        $this->entityManager->method('getClassMetadata')
            ->willReturnCallback(function ($className) {
                $metadata = new \Doctrine\ORM\Mapping\ClassMetadata($className);
                $metadata->name = $className; // Ensure name is set
                return $metadata;
            });

        // Mock the repository itself to override createQueryBuilder and findOneBy
        $this->repository = $this->getMockBuilder(ResetPasswordRequestRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['createQueryBuilder', 'findOneBy']) // List all methods that will be mocked
            ->getMock();
    }

    /**
     * Teste la méthode createResetPasswordRequest.
     *
     * Vérifie que la méthode retourne une instance correcte de ResetPasswordRequest
     * avec les propriétés attendues.
     */
    public function testCreateResetPasswordRequest(): void
    {
        $user = $this->createMock(Compte::class);
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = 'someSelector';
        $hashedToken = 'someHashedToken';

        $request = $this->repository->createResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertInstanceOf(ResetPasswordRequestInterface::class, $request);
        $this->assertInstanceOf(ResetPasswordRequest::class, $request);
        $this->assertSame($user, $request->getUser());
        $this->assertSame($expiresAt->getTimestamp(), $request->getExpiresAt()->getTimestamp());
        $this->assertSame($selector, $request->getSelector());
        $this->assertSame($hashedToken, $request->getHashedToken());
    }

    /**
     * Teste la méthode persistResetPasswordRequest.
     *
     * Vérifie que la méthode appelle correctement persist et flush sur l'EntityManager.
     */
    public function testPersistResetPasswordRequest(): void
    {
        $request = $this->createMock(ResetPasswordRequestInterface::class);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($request);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->repository->persistResetPasswordRequest($request);
    }

    /**
     * Teste la méthode findResetPasswordRequest.
     *
     * Vérifie que la méthode utilise findOneBy pour récupérer une requête
     * de réinitialisation de mot de passe par son sélecteur.
     */
    public function testFindResetPasswordRequest(): void
    {
        $selector = 'testSelector';
        $mockRequest = $this->createMock(ResetPasswordRequest::class);

        // Mock the findOneBy method directly on the repository mock
        $this->repository->method('findOneBy')
            ->with(['selector' => $selector])
            ->willReturn($mockRequest);

        $foundRequest = $this->repository->findResetPasswordRequest($selector);

        $this->assertSame($mockRequest, $foundRequest);
    }

    /**
     * Teste la méthode removeResetPasswordRequest.
     *
     * Vérifie que la méthode appelle correctement remove et flush sur l'EntityManager.
     */
    public function testRemoveResetPasswordRequest(): void
    {
        $request = $this->createMock(ResetPasswordRequestInterface::class);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($request);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->repository->removeResetPasswordRequest($request);
    }

    /**
     * Teste la méthode getUserIdentifier.
     *
     * Vérifie que la méthode retourne l'identifiant de l'utilisateur (email)
     * pour une instance de Compte valide.
     */
    public function testGetUserIdentifier(): void
    {
        $user = $this->createMock(Compte::class);
        $user->method('getEmail')->willReturn('test@example.com');

        $identifier = $this->repository->getUserIdentifier($user);

        $this->assertSame('test@example.com', $identifier);
    }

    /**
     * Teste que getUserIdentifier lève une exception pour un utilisateur invalide.
     *
     * Vérifie qu'une InvalidArgumentException est levée si l'objet utilisateur
     * n'est pas une instance de Compte.
     */
    public function testGetUserIdentifierThrowsExceptionForInvalidUser(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User object must be an instance of App\Entity\Compte.');

        $invalidUser = new \stdClass(); // Not an instance of Compte
        $this->repository->getUserIdentifier($invalidUser);
    }

    /**
     * Teste la méthode getMostRecentNonExpiredRequestDate.
     *
     * Vérifie que la méthode retourne la date d'expiration de la requête
     * la plus récente et non expirée pour un utilisateur donné.
     */
    public function testGetMostRecentNonExpiredRequestDate(): void
    {
        $user = $this->createMock(Compte::class);
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $mockRequest = $this->createMock(ResetPasswordRequest::class);
        $mockRequest->method('getExpiresAt')->willReturn($expiresAt);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // Mock createQueryBuilder directly on the repository mock
        $this->repository->method('createQueryBuilder')
            ->with('r')
            ->willReturn($queryBuilder);

        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getOneOrNullResult')->willReturn($mockRequest);

        $result = $this->repository->getMostRecentNonExpiredRequestDate($user);

        $this->assertSame($expiresAt, $result);
    }

    /**
     * Teste que getMostRecentNonExpiredRequestDate retourne null si aucune requête n'est trouvée.
     *
     * Vérifie que la méthode retourne null si aucune requête de réinitialisation
     * de mot de passe non expirée n'est trouvée pour l'utilisateur.
     */
    public function testGetMostRecentNonExpiredRequestDateReturnsNullIfNoRequest(): void
    {
        $user = $this->createMock(Compte::class);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // Mock createQueryBuilder directly on the repository mock
        $this->repository->method('createQueryBuilder')
            ->with('r')
            ->willReturn($queryBuilder);

        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getOneOrNullResult')->willReturn(null);

        $result = $this->repository->getMostRecentNonExpiredRequestDate($user);

        $this->assertNull($result);
    }

    /**
     * Teste la méthode removeExpiredResetPasswordRequests.
     *
     * Vérifie que la méthode supprime correctement les requêtes de réinitialisation
     * de mot de passe expirées et retourne le nombre d'éléments supprimés.
     */
    public function testRemoveExpiredResetPasswordRequests(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // Mock createQueryBuilder directly on the repository mock
        $this->repository->method('createQueryBuilder')
            ->with('r')
            ->willReturn($queryBuilder);

        $queryBuilder->method('delete')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('execute')->willReturn(5); // Simulate 5 deleted requests

        $deletedCount = $this->repository->removeExpiredResetPasswordRequests();

        $this->assertSame(5, $deletedCount);
    }
}
