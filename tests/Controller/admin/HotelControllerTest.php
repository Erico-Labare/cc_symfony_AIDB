<?php

namespace App\Tests\Controller\admin;

use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HotelControllerTest extends WebTestCase
{
    private ?\App\Entity\Compte $admin = null;

    private function setupData(): void
    {
        if ($this->admin === null) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $this->admin = $entityManager->getRepository(\App\Entity\Compte::class)
                ->findOneBy(['email' => 'admin@test.com']);
        }
    }

    public function testIndexWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/hotel');
        self::assertResponseRedirects();
    }

    public function testIndexWithNonAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(\App\Entity\Compte::class)
            ->findOneBy(['email' => 'test@test.com']);
        $client->loginUser($user);
        $client->request('GET', '/admin/hotel');
        self::assertResponseStatusCodeSame(403);
    }

    public function testIndexWithAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/hotel');
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/hotel/new');
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);

        $crawler = $client->request('GET', '/admin/hotel/new');
        $form = $crawler->selectButton('Save')->form([
            'hotel[nom]' => 'Hotel Luxe ' . time(),
            'hotel[adresse]' => '45 Avenue des Champs, Paris',
            'hotel[categorie]' => '*****',
        ]);
        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotels = $entityManager->getRepository(Hotel::class)
            ->findAll();
        self::assertGreaterThanOrEqual(1, count($hotels));
    }

    public function testShowExistingHotel(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test ' . time());
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $entityManager->persist($hotel);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/hotel/' . $hotel->getId());
        self::assertResponseStatusCodeSame(200);
    }

    public function testShowNonExistentHotel(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/hotel/99999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testEditFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel Original');
        $hotel->setAdresse('Original Address');
        $hotel->setCategorie('**');
        $entityManager->persist($hotel);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/hotel/' . $hotel->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    public function testEditFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel Original');
        $hotel->setAdresse('Original Address');
        $hotel->setCategorie('**');
        $entityManager->persist($hotel);
        $entityManager->flush();
        $id = $hotel->getId();

        $client->loginUser($this->admin);
        $crawler = $client->request('GET', '/admin/hotel/' . $id . '/edit');
        $form = $crawler->selectButton('Update')->form([
            'hotel[nom]' => 'Hotel Updated',
            'hotel[adresse]' => 'New Address',
            'hotel[categorie]' => '****',
        ]);
        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $updatedHotel = $entityManager->getRepository(Hotel::class)->find($id);
        self::assertSame('Hotel Updated', $updatedHotel->getNom());
    }

    public function testDeleteWithValidCsrfToken(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel to Delete');
        $hotel->setAdresse('Delete Me Street');
        $hotel->setCategorie('*');
        $entityManager->persist($hotel);
        $entityManager->flush();
        $id = $hotel->getId();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/hotel/' . $id);
        $form = $client->getCrawler()->selectButton('Delete')->form();

        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $deletedHotel = $entityManager->getRepository(Hotel::class)->find($id);
        self::assertNull($deletedHotel);
    }
}
