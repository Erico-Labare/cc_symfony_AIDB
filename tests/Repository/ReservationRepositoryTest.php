<?php

namespace App\Tests\Repository;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Tools\Pagination\Paginator; // Import Paginator

/**
 * Teste le ReservationRepository.
 *
 * Cette classe contient les tests unitaires pour vérifier le bon fonctionnement
 * des méthodes personnalisées du ReservationRepository, notamment la recherche
 * de réservations entre deux dates.
 */
class ReservationRepositoryTest extends TestCase
{
    private $entityManager;
    private $managerRegistry;
    private $repository;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Crée des mocks pour l'EntityManager et le ManagerRegistry, puis initialise
     * une instance mockée du ReservationRepository.
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

        // Mock the repository itself to override createQueryBuilder
        $this->repository = $this->getMockBuilder(ReservationRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['createQueryBuilder']) // List all methods that will be mocked
            ->getMock();
    }

    /**
     * Teste la méthode findBetweenDates.
     *
     * Vérifie que la méthode construit correctement la requête et retourne
     * les réservations attendues entre les dates spécifiées.
     */
    public function testFindBetweenDates(): void
    {
        $dateDebut = new \DateTimeImmutable('2023-01-01');
        $dateFin = new \DateTimeImmutable('2023-01-31');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // Mock createQueryBuilder directly on the repository mock
        $this->repository->method('createQueryBuilder')
            ->with('r')
            ->willReturn($queryBuilder);

        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $expectedReservations = [new Reservation(), new Reservation()];
        $query->method('getResult')->willReturn($expectedReservations);
        // No need to mock getDQL or iterate for this method as Paginator is not used

        $reservations = $this->repository->findBetweenDates($dateDebut, $dateFin);

        $this->assertIsArray($reservations);
        $this->assertCount(2, $reservations);
        $this->assertSame($expectedReservations, $reservations);
    }
}
