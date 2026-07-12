<?php

namespace App\Tests\Entity;

use App\Entity\Compte;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class CompteTest extends TestCase
{
    public function testCanCreateCompte(): void
    {
        $compte = new Compte();
        $this->assertInstanceOf(Compte::class, $compte);
        $this->assertNull($compte->getId()); // ID should be null before persisting
        $this->assertFalse($compte->isVerified());
        $this->assertSame(['ROLE_USER'], $compte->getRoles()); // Default role
    }

    public function testGetSetEmail(): void
    {
        $compte = new Compte();
        $email = 'test@example.com';
        $compte->setEmail($email);
        $this->assertSame($email, $compte->getEmail());
        $this->assertSame($email, $compte->getUserIdentifier());
    }

    public function testGetSetPassword(): void
    {
        $compte = new Compte();
        $password = 'hashed_password';
        $compte->setPassword($password);
        $this->assertSame($password, $compte->getPassword());
    }

    public function testGetSetRole(): void
    {
        $compte = new Compte();
        $this->assertSame(['ROLE_USER'], $compte->getRoles());

        $compte->setRole('ROLE_ADMIN');
        $this->assertSame('ROLE_ADMIN', $compte->getRole());
        $this->assertContains('ROLE_ADMIN', $compte->getRoles());
        $this->assertContains('ROLE_USER', $compte->getRoles()); // ROLE_USER should always be present
    }

    public function testEraseCredentials(): void
    {
        $compte = new Compte();
        // This method typically does nothing unless sensitive data is stored temporarily
        $compte->eraseCredentials();
        $this->assertTrue(true); // Just ensure it runs without error
    }

    public function testIsVerified(): void
    {
        $compte = new Compte();
        $this->assertFalse($compte->isVerified());

        $compte->setIsVerified(true);
        $this->assertTrue($compte->isVerified());

        $compte->setIsVerified(false);
        $this->assertFalse($compte->isVerified());
    }

    public function testAddRemoveReservation(): void
    {
        $compte = new Compte();
        $reservation = new Reservation();

        $this->assertCount(0, $compte->getReservations());

        $compte->addReservation($reservation);
        $this->assertCount(1, $compte->getReservations());
        $this->assertTrue($compte->getReservations()->contains($reservation));
        $this->assertSame($compte, $reservation->getCompte());

        $compte->removeReservation($reservation);
        $this->assertCount(0, $compte->getReservations());
        $this->assertFalse($compte->getReservations()->contains($reservation));
        $this->assertNull($reservation->getCompte());
    }

    public function testAddExistingReservationDoesNothing(): void
    {
        $compte = new Compte();
        $reservation = new Reservation();
        $compte->addReservation($reservation);
        $this->assertCount(1, $compte->getReservations());
        $compte->addReservation($reservation); // Add again
        $this->assertCount(1, $compte->getReservations()); // Should still be 1
    }

    public function testRemoveNonExistingReservationDoesNothing(): void
    {
        $compte = new Compte();
        $reservation = new Reservation();
        $this->assertCount(0, $compte->getReservations());
        $compte->removeReservation($reservation); // Remove non-existing
        $this->assertCount(0, $compte->getReservations()); // Should still be 0
    }
}
