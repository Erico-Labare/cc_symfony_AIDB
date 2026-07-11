<?php

namespace App\Tests\Service;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Repository\ChambreRepository;
use App\Repository\ReservationRepository;
use App\Services\DisponibiliteService;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;

/**
 * Teste le service de disponibilité des chambres.
 *
 * Cette classe contient les tests unitaires pour la logique de vérification de disponibilité
 * des chambres dans le DisponibiliteService.
 */
class DisponibiliteServiceTest extends BaseWebTestCase
{
    private DisponibiliteService $service;
    private ChambreRepository $chambreRepository;
    private ReservationRepository $reservationRepository;
    private EntityManagerInterface $entityManager;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le service de disponibilité, les dépôts et l'EntityManager.
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
        $container = self::getContainer();
        $this->service = $container->get(DisponibiliteService::class);
        $this->chambreRepository = $container->get(ChambreRepository::class);
        $this->reservationRepository = $container->get(ReservationRepository::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    /**
     * Teste la recherche de chambres disponibles avec des dates invalides (date de fin avant la date de début).
     *
     * Une InvalidArgumentException devrait être levée.
     */
    public function testFindAvailableRoomsWithNoDates(): void
    {
        $dateDebut = new \DateTime('2026-06-20');
        $dateFin = new \DateTime('2026-06-15');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->findAvailableRooms($dateDebut, $dateFin, 1);
    }

    /**
     * Teste la recherche de chambres disponibles lorsque aucune chambre ne correspond aux critères.
     *
     * Le résultat attendu est un tableau vide.
     */
    public function testFindAvailableRoomsEmpty(): void
    {
        $dateDebut = new \DateTime('2026-07-01');
        $dateFin = new \DateTime('2026-07-10');

        // Recherche de chambres avec un ID d'hôtel qui n'existe probablement pas
        $result = $this->service->findAvailableRooms($dateDebut, $dateFin, 9999);

        self::assertEmpty($result);
    }

    /**
     * Teste la méthode isRoomAvailable pour vérifier la disponibilité d'une chambre.
     *
     * Vérifie d'abord qu'une chambre est disponible, puis qu'elle ne l'est plus après une réservation.
     */
    public function testIsRoomAvailable(): void
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

        $dateDebut = new \DateTime('2026-06-15');
        $dateFin = new \DateTime('2026-06-20');

        // Test lorsque la chambre est disponible
        $isAvailable = $this->service->isRoomAvailable($chambre, $dateDebut, $dateFin);
        self::assertTrue($isAvailable, 'Room should be available initially.');

        // Crée une réservation pour rendre la chambre indisponible
        $reservation = new Reservation();
        $reservation->setChambre($chambre);
        $reservation->setClient($client);
        $reservation->setCompte($compte);
        $reservation->setDateDebut(new \DateTime('2026-06-17'));
        $reservation->setDateFin(new \DateTime('2026-06-19'));
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Re-fetch chambre after reservation
        $chambre = $this->entityManager->getRepository(Chambre::class)->find($chambre->getId());

        // Test lorsque la chambre est indisponible en raison de la réservation
        $isAvailableAfterReservation = $this->service->isRoomAvailable($chambre, new \DateTime('2026-06-18'), new \DateTime('2026-06-21'));
        self::assertFalse($isAvailableAfterReservation, 'Room should be unavailable after reservation.');
    }
}
