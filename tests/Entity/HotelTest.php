<?php

namespace App\Tests\Entity;

use App\Entity\Hotel;
use App\Entity\Chambre;
use PHPUnit\Framework\TestCase;

/**
 * Teste l'entité Hotel.
 *
 * Cette classe contient les tests unitaires pour vérifier le comportement de l'entité Hotel,
 * y compris la création, la modification des propriétés et la gestion des relations avec les chambres.
 */
class HotelTest extends TestCase
{
    /**
     * Teste la création d'une instance de Hotel.
     */
    public function testCanCreateHotel(): void
    {
        $hotel = new Hotel();
        $this->assertInstanceOf(Hotel::class, $hotel);
        $this->assertNull($hotel->getId()); // L'ID doit être null avant la persistance
    }

    /**
     * Teste les méthodes getNom() et setNom().
     */
    public function testGetSetNom(): void
    {
        $hotel = new Hotel();
        $nom = 'Grand Hotel';
        $hotel->setNom($nom);
        $this->assertSame($nom, $hotel->getNom());
    }

    /**
     * Teste les méthodes getAdresse() et setAdresse().
     */
    public function testGetSetAdresse(): void
    {
        $hotel = new Hotel();
        $adresse = '10 Rue de la Paix';
        $hotel->setAdresse($adresse);
        $this->assertSame($adresse, $hotel->getAdresse());
    }

    /**
     * Teste les méthodes getCategorie() et setCategorie().
     */
    public function testGetSetCategorie(): void
    {
        $hotel = new Hotel();
        $categorie = '****';
        $hotel->setCategorie($categorie);
        $this->assertSame($categorie, $hotel->getCategorie());
    }

    /**
     * Teste l'ajout et la suppression de chambres.
     */
    public function testAddRemoveChambre(): void
    {
        $hotel = new Hotel();
        $chambre = new Chambre();

        $this->assertCount(0, $hotel->getChambres());

        $hotel->addChambre($chambre);
        $this->assertCount(1, $hotel->getChambres());
        $this->assertTrue($hotel->getChambres()->contains($chambre));
        $this->assertSame($hotel, $chambre->getHotel());

        $hotel->removeChambre($chambre);
        $this->assertCount(0, $hotel->getChambres());
        $this->assertFalse($hotel->getChambres()->contains($chambre));
        $this->assertNull($chambre->getHotel());
    }

    /**
     * Teste qu'ajouter une chambre déjà existante ne modifie pas la collection.
     */
    public function testAddExistingChambreDoesNothing(): void
    {
        $hotel = new Hotel();
        $chambre = new Chambre();
        $hotel->addChambre($chambre);
        $this->assertCount(1, $hotel->getChambres());
        $hotel->addChambre($chambre); // Ajout à nouveau
        $this->assertCount(1, $hotel->getChambres()); // Doit toujours être 1
    }

    /**
     * Teste que la suppression d'une chambre non existante ne modifie pas la collection.
     */
    public function testRemoveNonExistingChambreDoesNothing(): void
    {
        $hotel = new Hotel();
        $chambre = new Chambre();
        $this->assertCount(0, $hotel->getChambres());
        $hotel->removeChambre($chambre); // Suppression d'une chambre non existante
        $this->assertCount(0, $hotel->getChambres()); // Doit toujours être 0
    }
}
