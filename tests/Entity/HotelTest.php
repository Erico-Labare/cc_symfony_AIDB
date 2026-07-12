<?php

namespace App\Tests\Entity;

use App\Entity\Hotel;
use App\Entity\Chambre;
use PHPUnit\Framework\TestCase;

class HotelTest extends TestCase
{
    public function testCanCreateHotel(): void
    {
        $hotel = new Hotel();
        $this->assertInstanceOf(Hotel::class, $hotel);
        $this->assertNull($hotel->getId()); // ID should be null before persisting
    }

    public function testGetSetNom(): void
    {
        $hotel = new Hotel();
        $nom = 'Grand Hotel';
        $hotel->setNom($nom);
        $this->assertSame($nom, $hotel->getNom());
    }

    public function testGetSetAdresse(): void
    {
        $hotel = new Hotel();
        $adresse = '10 Rue de la Paix';
        $hotel->setAdresse($adresse);
        $this->assertSame($adresse, $hotel->getAdresse());
    }

    public function testGetSetCategorie(): void
    {
        $hotel = new Hotel();
        $categorie = '****';
        $hotel->setCategorie($categorie);
        $this->assertSame($categorie, $hotel->getCategorie());
    }

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

    public function testAddExistingChambreDoesNothing(): void
    {
        $hotel = new Hotel();
        $chambre = new Chambre();
        $hotel->addChambre($chambre);
        $this->assertCount(1, $hotel->getChambres());
        $hotel->addChambre($chambre); // Add again
        $this->assertCount(1, $hotel->getChambres()); // Should still be 1
    }

    public function testRemoveNonExistingChambreDoesNothing(): void
    {
        $hotel = new Hotel();
        $chambre = new Chambre();
        $this->assertCount(0, $hotel->getChambres());
        $hotel->removeChambre($chambre); // Remove non-existing
        $this->assertCount(0, $hotel->getChambres()); // Should still be 0
    }
}
