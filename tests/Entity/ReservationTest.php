<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Entity\Chambre;
use PHPUnit\Framework\TestCase;

/**
 * Teste l'entité Reservation.
 *
 * Cette classe contient les tests unitaires pour vérifier le comportement de l'entité Reservation,
 * y compris la création, la modification des propriétés (dates, commentaire)
 * et la gestion des relations avec le compte, le client et la chambre.
 */
class ReservationTest extends TestCase
{
    /**
     * Teste la création d'une instance de Reservation.
     */
    public function testCanCreateReservation(): void
    {
        $reservation = new Reservation();
        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertNull($reservation->getId()); // L'ID doit être null avant la persistance
    }

    /**
     * Teste les méthodes getDateDebut() et setDateDebut().
     */
    public function testGetSetDateDebut(): void
    {
        $reservation = new Reservation();
        $date = new \DateTimeImmutable('tomorrow');
        $reservation->setDateDebut($date);
        $this->assertSame($date, $reservation->getDateDebut());
    }

    /**
     * Teste les méthodes getDateFin() et setDateFin().
     */
    public function testGetSetDateFin(): void
    {
        $reservation = new Reservation();
        $date = new \DateTimeImmutable('next week');
        $reservation->setDateFin($date);
        $this->assertSame($date, $reservation->getDateFin());
    }

    /**
     * Teste les méthodes getCommentaire() et setCommentaire().
     */
    public function testGetSetCommentaire(): void
    {
        $reservation = new Reservation();
        $commentaire = 'Lit bébé et chambre non-fumeur';
        $reservation->setCommentaire($commentaire);
        $this->assertSame($commentaire, $reservation->getCommentaire());

        $reservation->setCommentaire(null);
        $this->assertNull($reservation->getCommentaire());
    }

    /**
     * Teste les méthodes getCompte() et setCompte().
     */
    public function testGetSetCompte(): void
    {
        $reservation = new Reservation();
        $compte = new Compte();
        $reservation->setCompte($compte);
        $this->assertSame($compte, $reservation->getCompte());

        $reservation->setCompte(null);
        $this->assertNull($reservation->getCompte());
    }

    /**
     * Teste les méthodes getClient() et setClient().
     */
    public function testGetSetClient(): void
    {
        $reservation = new Reservation();
        $client = new Client();
        $reservation->setClient($client);
        $this->assertSame($client, $reservation->getClient());

        $reservation->setClient(null);
        $this->assertNull($reservation->getClient());
    }

    /**
     * Teste les méthodes getChambre() et setChambre().
     */
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
