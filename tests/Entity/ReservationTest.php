<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Entity\Chambre;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testCanCreateReservation(): void
    {
        $reservation = new Reservation();
        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertNull($reservation->getId()); // ID should be null before persisting
    }

    public function testGetSetDateDebut(): void
    {
        $reservation = new Reservation();
        $date = new \DateTimeImmutable('tomorrow');
        $reservation->setDateDebut($date);
        $this->assertSame($date, $reservation->getDateDebut());
    }

    public function testGetSetDateFin(): void
    {
        $reservation = new Reservation();
        $date = new \DateTimeImmutable('next week');
        $reservation->setDateFin($date);
        $this->assertSame($date, $reservation->getDateFin());
    }

    public function testGetSetCommentaire(): void
    {
        $reservation = new Reservation();
        $commentaire = 'Lit bébé et chambre non-fumeur';
        $reservation->setCommentaire($commentaire);
        $this->assertSame($commentaire, $reservation->getCommentaire());

        $reservation->setCommentaire(null);
        $this->assertNull($reservation->getCommentaire());
    }

    public function testGetSetCompte(): void
    {
        $reservation = new Reservation();
        $compte = new Compte();
        $reservation->setCompte($compte);
        $this->assertSame($compte, $reservation->getCompte());

        $reservation->setCompte(null);
        $this->assertNull($reservation->getCompte());
    }

    public function testGetSetClient(): void
    {
        $reservation = new Reservation();
        $client = new Client();
        $reservation->setClient($client);
        $this->assertSame($client, $reservation->getClient());

        $reservation->setClient(null);
        $this->assertNull($reservation->getClient());
    }

    public function testGetSetChambre(): void
    {
        $reservation = new Reservation();
        $chambre = new Chambre();
        $reservation->setChambre($chambre);
        $this->assertSame($chambre, $reservation->getChambre());

        $reservation->setChambre(null);
        $this->assertNull($reservation->getChambre());
    }
}
