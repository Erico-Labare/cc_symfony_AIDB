<?php

namespace App\Tests\Entity;

use App\Entity\Compte;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

/**
 * Teste l'entité Compte.
 *
 * Cette classe contient les tests unitaires pour vérifier le comportement de l'entité Compte,
 * y compris la création, la modification des propriétés (email, mot de passe, rôles, vérification)
 * et la gestion des relations avec les réservations.
 */
class CompteTest extends TestCase
{
    /**
     * Teste la création d'une instance de Compte.
     */
    public function testCanCreateCompte(): void
    {
        $compte = new Compte();
        $this->assertInstanceOf(Compte::class, $compte);
        $this->assertNull($compte->getId()); // L'ID doit être null avant la persistance
        $this->assertFalse($compte->isVerified()); // Par défaut, le compte ne doit pas être vérifié
        $this->assertSame(['ROLE_USER'], $compte->getRoles()); // Le rôle par défaut doit être ROLE_USER
    }

    /**
     * Teste les méthodes getEmail(), setEmail() et getUserIdentifier().
     */
    public function testGetSetEmail(): void
    {
        $compte = new Compte();
        $email = 'test@example.com';
        $compte->setEmail($email);
        $this->assertSame($email, $compte->getEmail());
        $this->assertSame($email, $compte->getUserIdentifier()); // L'identifiant utilisateur doit être l'email
    }

    /**
     * Teste les méthodes getPassword() et setPassword().
     */
    public function testGetSetPassword(): void
    {
        $compte = new Compte();
        $password = 'hashed_password';
        $compte->setPassword($password);
        $this->assertSame($password, $compte->getPassword());
    }

    /**
     * Teste les méthodes getRole() et setRole(), ainsi que getRoles().
     */
    public function testGetSetRole(): void
    {
        $compte = new Compte();
        $this->assertSame(['ROLE_USER'], $compte->getRoles()); // Rôle par défaut

        $compte->setRole('ROLE_ADMIN');
        $this->assertSame('ROLE_ADMIN', $compte->getRole()); // Vérifie le rôle principal
        $this->assertContains('ROLE_ADMIN', $compte->getRoles()); // Vérifie que ROLE_ADMIN est dans la liste des rôles
        $this->assertContains('ROLE_USER', $compte->getRoles()); // ROLE_USER doit toujours être présent
    }

    /**
     * Teste la méthode eraseCredentials().
     * Cette méthode est généralement vide ou nettoie des données sensibles temporaires.
     */
    public function testEraseCredentials(): void
    {
        $compte = new Compte();
        // Cette méthode ne fait généralement rien à moins que des données sensibles ne soient stockées temporairement
        $compte->eraseCredentials();
        $this->assertTrue(true); // S'assure juste qu'elle s'exécute sans erreur
    }

    /**
     * Teste les méthodes isVerified() et setIsVerified().
     */
    public function testIsVerified(): void
    {
        $compte = new Compte();
        $this->assertFalse($compte->isVerified()); // Par défaut, non vérifié

        $compte->setIsVerified(true);
        $this->assertTrue($compte->isVerified()); // Doit être vérifié après avoir appelé setIsVerified(true)

        $compte->setIsVerified(false);
        $this->assertFalse($compte->isVerified()); // Doit être non vérifié après avoir appelé setIsVerified(false)
    }

    /**
     * Teste l'ajout et la suppression de réservations.
     */
    public function testAddRemoveReservation(): void
    {
        $compte = new Compte();
        $reservation = new Reservation();

        $this->assertCount(0, $compte->getReservations()); // Aucune réservation au début

        $compte->addReservation($reservation);
        $this->assertCount(1, $compte->getReservations()); // Une réservation après l'ajout
        $this->assertTrue($compte->getReservations()->contains($reservation)); // La réservation doit être présente
        $this->assertSame($compte, $reservation->getCompte()); // Le compte de la réservation doit être celui-ci

        $compte->removeReservation($reservation);
        $this->assertCount(0, $compte->getReservations()); // Aucune réservation après la suppression
        $this->assertFalse($compte->getReservations()->contains($reservation)); // La réservation ne doit plus être présente
        $this->assertNull($reservation->getCompte()); // Le compte de la réservation doit être null
    }

    /**
     * Teste qu'ajouter une réservation déjà existante ne modifie pas la collection.
     */
    public function testAddExistingReservationDoesNothing(): void
    {
        $compte = new Compte();
        $reservation = new Reservation();
        $compte->addReservation($reservation);
        $this->assertCount(1, $compte->getReservations());
        $compte->addReservation($reservation); // Ajout à nouveau
        $this->assertCount(1, $compte->getReservations()); // Doit toujours être 1
    }

    /**
     * Teste que la suppression d'une réservation non existante ne modifie pas la collection.
     */
    public function testRemoveNonExistingReservationDoesNothing(): void
    {
        $compte = new Compte();
        $reservation = new Reservation();
        $this->assertCount(0, $compte->getReservations());
        $compte->removeReservation($reservation); // Suppression d'une réservation non existante
        $this->assertCount(0, $compte->getReservations()); // Doit toujours être 0
    }
}
