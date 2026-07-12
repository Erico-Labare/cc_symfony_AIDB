<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testCanCreateClient(): void
    {
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
        $this->assertNull($client->getId()); // ID should be null before persisting
    }

    public function testGetSetNom(): void
    {
        $client = new Client();
        $nom = 'John Doe';
        $client->setNom($nom);
        $this->assertSame($nom, $client->getNom());
    }

    public function testGetSetAdresse(): void
    {
        $client = new Client();
        $adresse = '123 Main St';
        $client->setAdresse($adresse);
        $this->assertSame($adresse, $client->getAdresse());
    }

    public function testGetSetEmail(): void
    {
        $client = new Client();
        $email = 'john.doe@example.com';
        $client->setEmail($email);
        $this->assertSame($email, $client->getEmail());
    }

    public function testGetSetTelephone(): void
    {
        $client = new Client();
        $telephone = '0123456789';
        $client->setTelephone($telephone);
        $this->assertSame($telephone, $client->getTelephone());
    }

    public function testAddRemoveReservation(): void
    {
        $client = new Client();
        $reservation = new Reservation();

        $this->assertCount(0, $client->getReservations());

        $client->addReservation($reservation);
        $this->assertCount(1, $client->getReservations());
        $this->assertTrue($client->getReservations()->contains($reservation));
        $this->assertSame($client, $reservation->getClient());

        $client->removeReservation($reservation);
        $this->assertCount(0, $client->getReservations());
        $this->assertFalse($client->getReservations()->contains($reservation));
        $this->assertNull($reservation->getClient());
    }

    public function testAddExistingReservationDoesNothing(): void
    {
        $client = new Client();
        $reservation = new Reservation();
        $client->addReservation($reservation);
        $this->assertCount(1, $client->getReservations());
        $client->addReservation($reservation); // Add again
        $this->assertCount(1, $client->getReservations()); // Should still be 1
    }

    public function testRemoveNonExistingReservationDoesNothing(): void
    {
        $client = new Client();
        $reservation = new Reservation();
        $this->assertCount(0, $client->getReservations());
        $client->removeReservation($reservation); // Remove non-existing
        $this->assertCount(0, $client->getReservations()); // Should still be 0
    }
}
