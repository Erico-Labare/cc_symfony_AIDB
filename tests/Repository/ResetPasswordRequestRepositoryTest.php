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

class ResetPasswordRequestRepositoryTest extends TestCase
{
    private $entityManager;
    private $managerRegistry;
    private $repository;

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

    public function testGetUserIdentifier(): void
    {
        $user = $this->createMock(Compte::class);
        $user->method('getEmail')->willReturn('test@example.com');

        $identifier = $this->repository->getUserIdentifier($user);

        $this->assertSame('test@example.com', $identifier);
    }

    public function testGetUserIdentifierThrowsExceptionForInvalidUser(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User object must be an instance of App\Entity\Compte.');

        $invalidUser = new \stdClass(); // Not an instance of Compte
        $this->repository->getUserIdentifier($invalidUser);
    }

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
