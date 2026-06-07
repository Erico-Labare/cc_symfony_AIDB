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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DisponibiliteServiceTest extends KernelTestCase
{
    private DisponibiliteService $service;
    private ChambreRepository $chambreRepository;
    private ReservationRepository $reservationRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->service = $container->get(DisponibiliteService::class);
        $this->chambreRepository = $container->get(ChambreRepository::class);
        $this->reservationRepository = $container->get(ReservationRepository::class);
    }

    public function testFindAvailableRoomsWithNoDates(): void
    {
        $dateDebut = new \DateTime('2026-06-20');
        $dateFin = new \DateTime('2026-06-15');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->findAvailableRooms($dateDebut, $dateFin, 1);
    }

    public function testFindAvailableRoomsEmpty(): void
    {
        $dateDebut = new \DateTime('2026-07-01');
        $dateFin = new \DateTime('2026-07-10');

        $result = $this->service->findAvailableRooms($dateDebut, $dateFin, 9999);

        self::assertEmpty($result);
    }

    public function testIsRoomAvailable(): void
    {
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        $hotel = new Hotel();
        $hotel->setNom('Hotel Test');
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $entityManager->persist($chambre);

        $dateDebut = new \DateTime('2026-06-15');
        $dateFin = new \DateTime('2026-06-20');

        $entityManager->flush();

        $isAvailable = $this->service->isRoomAvailable($chambre, $dateDebut, $dateFin);

        self::assertTrue($isAvailable);
    }
}
