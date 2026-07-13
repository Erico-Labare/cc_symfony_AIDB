<?php

namespace App\Tests\Entity;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

/**
 * Teste l'entité Chambre.
 *
 * Cette classe contient les tests unitaires pour vérifier le comportement de l'entité Chambre,
 * y compris la création, la modification des propriétés (étage, type, nombre de lits)
 * et la gestion des relations avec l'hôtel et les réservations.
 */
class ChambreTest extends TestCase
{
    /**
     * Teste la création d'une instance de Chambre.
     */
    public function testCanCreateChambre(): void
    {
        $chambre = new Chambre();
        $this->assertInstanceOf(Chambre::class, $chambre);
        $this->assertNull($chambre->getId()); // L'ID doit être null avant la persistance
    }

    /**
     * Teste les méthodes getEtage() et setEtage().
     */
    public function testGetSetEtage(): void
    {
        $chambre = new Chambre();
        $etage = 5;
        $chambre->setEtage($etage);
        $this->assertSame($etage, $chambre->getEtage());
    }

    /**
     * Teste les méthodes getType() et setType().
     */
    public function testGetSetType(): void
    {
        $chambre = new Chambre();
        $type = 'double';
        $chambre->setType($type);
        $this->assertSame($type, $chambre->getType());
    }

    /**
     * Teste les méthodes getNombreLit() et setNombreLit().
     */
    public function testGetSetNombreLit(): void
    {
        $chambre = new Chambre();
        $nombreLit = 2;
        $chambre->setNombreLit($nombreLit);
        $this->assertSame($nombreLit, $chambre->getNombreLit());
    }

    /**
     * Teste les méthodes getHotel() et setHotel().
     */
    public function testGetSetHotel(): void
    {
        $chambre = new Chambre();
        $hotel = new Hotel();
        $chambre->setHotel($hotel);
        $this->assertSame($hotel, $chambre->getHotel());

        $chambre->setHotel(null);
        $this->assertNull($chambre->getHotel());
    }

    /**
     * Teste l'ajout et la suppression de réservations.
     */
    public function testAddRemoveReservation(): void
    {
        $chambre = new Chambre();
        $reservation = new Reservation();

        $this->assertCount(0, $chambre->getReservations()); // Aucune réservation au début

        $chambre->addReservation($reservation);
        $this->assertCount(1, $chambre->getReservations()); // Une réservation après l'ajout
        $this->assertTrue($chambre->getReservations()->contains($reservation)); // La réservation doit être présente
        $this->assertSame($chambre, $reservation->getChambre()); // La chambre de la réservation doit être celle-ci

        $chambre->removeReservation($reservation);
        $this->assertCount(0, $chambre->getReservations()); // Aucune réservation après la suppression
        $this->assertFalse($chambre->getReservations()->contains($reservation)); // La réservation ne doit plus être présente
        $this->assertNull($reservation->getChambre()); // La chambre de la réservation doit être null
    }

    /**
     * Teste qu'ajouter une réservation déjà existante ne modifie pas la collection.
     */
    public function testAddExistingReservationDoesNothing(): void
    {
        $chambre = new Chambre();
        $reservation = new Reservation();
        $chambre->addReservation($reservation);
        $this->assertCount(1, $chambre->getReservations());
        $chambre->addReservation($reservation); // Ajout à nouveau
        $this->assertCount(1, $chambre->getReservations()); // Doit toujours être 1
    }

    /**
     * Teste que la suppression d'une réservation non existante ne modifie pas la collection.
     */
    public function testRemoveNonExistingReservationDoesNothing(): void
    {
        $chambre = new Chambre();
        $reservation = new Reservation();
        $this->assertCount(0, $chambre->getReservations());
        $chambre->removeReservation($reservation); // Suppression d'une réservation non existante
        $this->assertCount(0, $chambre->getReservations()); // Doit toujours être 0
    }
}
