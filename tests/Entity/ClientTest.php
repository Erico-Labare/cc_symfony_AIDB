<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

/**
 * Teste l'entité Client.
 *
 * Cette classe contient les tests unitaires pour vérifier le comportement de l'entité Client,
 * y compris la création, la modification des propriétés et la gestion des relations avec les réservations.
 */
class ClientTest extends TestCase
{
    /**
     * Teste la création d'une instance de Client.
     */
    public function testCanCreateClient(): void
    {
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
        $this->assertNull($client->getId()); // L'ID doit être null avant la persistance
    }

    /**
     * Teste les méthodes getNom() et setNom().
     */
    public function testGetSetNom(): void
    {
        $client = new Client();
        $nom = 'John Doe';
        $client->setNom($nom);
        $this->assertSame($nom, $client->getNom());
    }

    /**
     * Teste les méthodes getAdresse() et setAdresse().
     */
    public function testGetSetAdresse(): void
    {
        $client = new Client();
        $adresse = '123 Main St';
        $client->setAdresse($adresse);
        $this->assertSame($adresse, $client->getAdresse());
    }

    /**
     * Teste les méthodes getEmail() et setEmail().
     */
    public function testGetSetEmail(): void
    {
        $client = new Client();
        $email = 'john.doe@example.com';
        $client->setEmail($email);
        $this->assertSame($email, $client->getEmail());
    }

    /**
     * Teste les méthodes getTelephone() et setTelephone().
     */
    public function testGetSetTelephone(): void
    {
        $client = new Client();
        $telephone = '0123456789';
        $client->setTelephone($telephone);
        $this->assertSame($telephone, $client->getTelephone());
    }

    /**
     * Teste l'ajout et la suppression de réservations.
     */
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

    /**
     * Teste qu'ajouter une réservation déjà existante ne modifie pas la collection.
     */
    public function testAddExistingReservationDoesNothing(): void
    {
        $client = new Client();
        $reservation = new Reservation();
        $client->addReservation($reservation);
        $this->assertCount(1, $client->getReservations());
        $client->addReservation($reservation); // Ajout à nouveau
        $this->assertCount(1, $client->getReservations()); // Doit toujours être 1
    }

    /**
     * Teste que la suppression d'une réservation non existante ne modifie pas la collection.
     */
    public function testRemoveNonExistingReservationDoesNothing(): void
    {
        $client = new Client();
        $reservation = new Reservation();
        $this->assertCount(0, $client->getReservations());
        $client->removeReservation($reservation); // Suppression d'une réservation non existante
        $this->assertCount(0, $client->getReservations()); // Doit toujours être 0
    }
}
