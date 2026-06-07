<?php

namespace App\Tests\Service;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Services\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReservationServiceTest extends KernelTestCase
{
    private ReservationService $service;
    private EntityManagerInterface $entityManager;
    private static int $testCounter = 0;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->service = $container->get(ReservationService::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
        self::$testCounter++;
    }

    public function testCreateReservationWithInvalidDates(): void
    {
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test');
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $this->entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $this->entityManager->persist($chambre);

        $client = new Client();
        $client->setNom('Test Client');
        $client->setEmail('client' . self::$testCounter . '@test.com');
        $client->setAdresse('123 Rue Client');
        $client->setTelephone('0123456789');
        $this->entityManager->persist($client);

        $compte = new Compte();
        $compte->setEmail('user' . self::$testCounter . '@test.com');
        $compte->setPassword('hashed_password');
        $this->entityManager->persist($compte);

        $this->entityManager->flush();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->createReservation(
            $chambre,
            $client,
            $compte,
            new \DateTime('2026-06-20'),
            new \DateTime('2026-06-15'),
        );
    }

    public function testCreateReservationSuccess(): void
    {
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test');
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $this->entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $this->entityManager->persist($chambre);

        $client = new Client();
        $client->setNom('Test Client');
        $client->setEmail('client' . self::$testCounter . '@test.com');
        $client->setAdresse('123 Rue Client');
        $client->setTelephone('0123456789');
        $this->entityManager->persist($client);

        $compte = new Compte();
        $compte->setEmail('user' . self::$testCounter . '@test.com');
        $compte->setPassword('hashed_password');
        $this->entityManager->persist($compte);

        $this->entityManager->flush();

        $dateDebut = new \DateTime('2026-06-15');
        $dateFin = new \DateTime('2026-06-20');

        $reservation = $this->service->createReservation(
            $chambre,
            $client,
            $compte,
            $dateDebut,
            $dateFin,
            'Demande spéciale',
        );

        self::assertInstanceOf(Reservation::class, $reservation);
        self::assertSame($chambre, $reservation->getChambre());
        self::assertSame($client, $reservation->getClient());
        self::assertSame($compte, $reservation->getCompte());
        self::assertEquals($dateDebut, $reservation->getDateDebut());
        self::assertEquals($dateFin, $reservation->getDateFin());
        self::assertSame('Demande spéciale', $reservation->getCommentaire());
    }

    public function testCreateReservationUnavailableRoom(): void
    {
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test');
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $this->entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $this->entityManager->persist($chambre);

        $client = new Client();
        $client->setNom('Test Client');
        $client->setEmail('client' . self::$testCounter . '@test.com');
        $client->setAdresse('123 Rue Client');
        $client->setTelephone('0123456789');
        $this->entityManager->persist($client);

        $compte = new Compte();
        $compte->setEmail('user' . self::$testCounter . '@test.com');
        $compte->setPassword('hashed_password');
        $this->entityManager->persist($compte);

        $reservation = new Reservation();
        $reservation->setChambre($chambre);
        $reservation->setClient($client);
        $reservation->setCompte($compte);
        $reservation->setDateDebut(new \DateTime('2026-06-15'));
        $reservation->setDateFin(new \DateTime('2026-06-20'));
        $this->entityManager->persist($reservation);

        $this->entityManager->flush();

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
