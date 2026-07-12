<?php

namespace App\Tests\Service;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Services\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Teste le service de réservation.
 *
 * Cette classe contient les tests unitaires pour la logique de création de réservation
 * dans le ReservationService.
 */
class ReservationServiceTest extends BaseWebTestCase
{
    private ReservationService $service;
    private EntityManagerInterface $entityManager;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le service de réservation et l'EntityManager.
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $container = self::getContainer();
        $this->service = $container->get(ReservationService::class); // Reverted to original autowiring
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    /**
     * Teste la création d'une réservation avec des dates invalides (date de fin avant la date de début).
     *
     * Une InvalidArgumentException devrait être levée.
     */
    public function testCreateReservationWithInvalidDates(): void
    {
        $uniqueId = uniqid();

        // Création des entités nécessaires pour le test
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test ' . $uniqueId);
        $hotel->setAdresse('123 Rue Test ' . $uniqueId);
        $hotel->setCategorie('***');
        $this->entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $this->entityManager->persist($chambre);

        $client = new Client();
        $client->setNom('Test Client ' . $uniqueId);
        $client->setEmail('client' . $uniqueId . '@test.com');
        $client->setAdresse('123 Rue Client ' . $uniqueId);
        $client->setTelephone('0123456789');
        $this->entityManager->persist($client);

        $compte = new Compte();
        $compte->setEmail('user' . $uniqueId . '@test.com');
        $compte->setPassword('hashed_password');
        $compte->setRole('ROLE_USER');
        $compte->setIsVerified(true);
        $this->entityManager->persist($compte);

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Re-fetch entities after clear to ensure they are managed
        $chambre = $this->entityManager->getRepository(Chambre::class)->find($chambre->getId());
        $client = $this->entityManager->getRepository(Client::class)->find($client->getId());
        $compte = $this->entityManager->getRepository(Compte::class)->find($compte->getId());

        // On s'attend à une exception car la date de fin est antérieure à la date de début
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createReservation(
            $chambre,
            $client,
            $compte,
            new \DateTime('2026-06-20'),
            new \DateTime('2026-06-15'),
        );
    }

    /**
     * Teste la création réussie d'une réservation.
     *
     * Vérifie que la réservation est correctement créée et que ses propriétés sont définies.
     */
    public function testCreateReservationSuccess(): void
    {
        $uniqueId = uniqid();

        // Création des entités nécessaires pour le test
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test ' . $uniqueId);
        $hotel->setAdresse('123 Rue Test ' . $uniqueId);
        $hotel->setCategorie('***');
        $this->entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $this->entityManager->persist($chambre);

        $client = new Client();
        $client->setNom('Test Client ' . $uniqueId);
        $client->setEmail('client' . $uniqueId . '@test.com');
        $client->setAdresse('123 Rue Client ' . $uniqueId);
        $client->setTelephone('0123456789');
        $this->entityManager->persist($client);

        $compte = new Compte();
        $compte->setEmail('user' . $uniqueId . '@test.com');
        $compte->setPassword('hashed_password');
        $compte->setRole('ROLE_USER');
        $compte->setIsVerified(true);
        $this->entityManager->persist($compte);

        $this->entityManager->flush();
        $entityManagerId = $chambre->getId(); // Store ID before clear
        $this->entityManager->clear();

        // Re-fetch entities after clear to ensure they are managed
        $chambre = $this->entityManager->getRepository(Chambre::class)->find($entityManagerId);
        $client = $this->entityManager->getRepository(Client::class)->find($client->getId());
        $compte = $this->entityManager->getRepository(Compte::class)->find($compte->getId());

        // Utilise des dates éloignées dans le futur pour éviter les conflits
        $dateDebut = new \DateTime('2030-01-01');
        $dateFin = new \DateTime('2030-01-05');

        // Crée la réservation via le service
        $reservation = $this->service->createReservation(
            $chambre,
            $client,
            $compte,
            $dateDebut,
            $dateFin,
            'Demande spéciale',
        );

        // Assertions pour vérifier la création correcte de la réservation
        self::assertInstanceOf(Reservation::class, $reservation);
        self::assertSame($chambre->getId(), $reservation->getChambre()->getId());
        self::assertSame($client->getId(), $reservation->getClient()->getId());
        self::assertSame($compte->getId(), $reservation->getCompte()->getId());
        self::assertEquals($dateDebut, $reservation->getDateDebut());
        self::assertEquals($dateFin, $reservation->getDateFin());
        self::assertSame('Demande spéciale', $reservation->getCommentaire());
    }

    /**
     * Teste la création d'une réservation pour une chambre déjà occupée.
     *
     * Une InvalidArgumentException devrait être levée avec un message spécifique.
     */
    public function testCreateReservationUnavailableRoom(): void
    {
        $uniqueId = uniqid();

        // Création des entités nécessaires pour le test
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test ' . $uniqueId);
        $hotel->setAdresse('123 Rue Test ' . $uniqueId);
        $hotel->setCategorie('***');
        $this->entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $this->entityManager->persist($chambre);

        $client = new Client();
        $client->setNom('Test Client ' . $uniqueId);
        $client->setEmail('client' . $uniqueId . '@test.com');
        $client->setAdresse('123 Rue Client ' . $uniqueId);
        $client->setTelephone('0123456789');
        $this->entityManager->persist($client);

        $compte = new Compte();
        $compte->setEmail('user' . $uniqueId . '@test.com');
        $compte->setPassword('hashed_password');
        $compte->setRole('ROLE_USER');
        $compte->setIsVerified(true);
        $this->entityManager->persist($compte);

        // Crée une réservation existante pour rendre la chambre indisponible
        $reservation = new Reservation();
        $reservation->setChambre($chambre);
        $reservation->setClient($client);
        $reservation->setCompte($compte);
        $reservation->setDateDebut(new \DateTime('2026-06-15'));
        $reservation->setDateFin(new \DateTime('2026-06-20'));
        $this->entityManager->persist($reservation);

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Re-fetch entities after clear to ensure they are managed
        $chambre = $this->entityManager->getRepository(Chambre::class)->find($chambre->getId());
        $client = $this->entityManager->getRepository(Client::class)->find($client->getId());
        $compte = $this->entityManager->getRepository(Compte::class)->find($compte->getId());

        // On s'attend à une exception car la chambre n'est pas disponible pour les dates demandées
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('n\'est pas disponible');

        $this->service->createReservation(
            $chambre,
            $client,
            $compte,
            new \DateTime('2026-06-18'),
            new \DateTime('2026-06-22'),
        );
    }
}
