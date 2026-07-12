<?php

namespace App\Tests\Entity;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class ChambreTest extends TestCase
{
    public function testCanCreateChambre(): void
    {
        $chambre = new Chambre();
        $this->assertInstanceOf(Chambre::class, $chambre);
        $this->assertNull($chambre->getId()); // ID should be null before persisting
    }

    public function testGetSetEtage(): void
    {
        $chambre = new Chambre();
        $etage = 5;
        $chambre->setEtage($etage);
        $this->assertSame($etage, $chambre->getEtage());
    }

    public function testGetSetType(): void
    {
        $chambre = new Chambre();
        $type = 'double';
        $chambre->setType($type);
        $this->assertSame($type, $chambre->getType());
    }

    public function testGetSetNombreLit(): void
    {
        $chambre = new Chambre();
        $nombreLit = 2;
        $chambre->setNombreLit($nombreLit);
        $this->assertSame($nombreLit, $chambre->getNombreLit());
    }

    public function testGetSetHotel(): void
    {
        $chambre = new Chambre();
        $hotel = new Hotel();
        $chambre->setHotel($hotel);
        $this->assertSame($hotel, $chambre->getHotel());

        $chambre->setHotel(null);
        $this->assertNull($chambre->getHotel());
    }

    public function testAddRemoveReservation(): void
    {
        $chambre = new Chambre();
        $reservation = new Reservation();

        $this->assertCount(0, $chambre->getReservations());

        $chambre->addReservation($reservation);
        $this->assertCount(1, $chambre->getReservations());
        $this->assertTrue($chambre->getReservations()->contains($reservation));
        $this->assertSame($chambre, $reservation->getChambre());

        $chambre->removeReservation($reservation);
        $this->assertCount(0, $chambre->getReservations());
        $this->assertFalse($chambre->getReservations()->contains($reservation));
        $this->assertNull($reservation->getChambre());
    }

    public function testAddExistingReservationDoesNothing(): void
    {
        $chambre = new Chambre();
        $reservation = new Reservation();
        $chambre->addReservation($reservation);
        $this->assertCount(1, $chambre->getReservations());
        $chambre->addReservation($reservation); // Add again
        $this->assertCount(1, $chambre->getReservations()); // Should still be 1
    }

    public function testRemoveNonExistingReservationDoesNothing(): void
    {
        $chambre = new Chambre();
        $reservation = new Reservation();
        $this->assertCount(0, $chambre->getReservations());
        $chambre->removeReservation($reservation); // Remove non-existing
        $this->assertCount(0, $chambre->getReservations()); // Should still be 0
    }
}
