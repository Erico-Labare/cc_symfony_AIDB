<?php

namespace App\Tests\Controller\admin;

use App\Entity\Chambre;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ChambreControllerTest extends WebTestCase
{
    private ?\App\Entity\Compte $admin = null;
    private ?Hotel $hotel = null;

    private function setupData(): void
    {
        if ($this->admin === null) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $this->admin = $entityManager->getRepository(\App\Entity\Compte::class)
                ->findOneBy(['email' => 'admin@test.com']);

            $this->hotel = new Hotel();
            $this->hotel->setNom('Hotel Test Chambre');
            $this->hotel->setAdresse('123 Rue Test');
            $this->hotel->setCategorie('****');
            $entityManager->persist($this->hotel);
            $entityManager->flush();
        }
    }

    public function testIndexWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/chambre');
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
        $client->request('GET', '/admin/chambre');
        self::assertResponseStatusCodeSame(403);
    }

    public function testIndexWithAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/chambre');
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/chambre/new');
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);

        $crawler = $client->request('GET', '/admin/chambre/new');
        $form = $crawler->selectButton('Save')->form([
            'chambre[etage]' => '3',
            'chambre[type]' => 'double',
            'chambre[nombreLit]' => '2',
            'chambre[hotel]' => $this->hotel->getId(),
        ]);
        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambres = $entityManager->getRepository(Chambre::class)->findBy(['etage' => 3]);
        self::assertGreaterThanOrEqual(1, count($chambres));
    }

    public function testShowExistingChambre(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('single');
        $chambre->setNombreLit(1);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/chambre/' . $chambre->getId());
        self::assertResponseStatusCodeSame(200);
    }

    public function testShowNonExistentChambre(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/chambre/99999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testEditFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(1);
        $chambre->setType('suite');
        $chambre->setNombreLit(4);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/chambre/' . $chambre->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    public function testEditFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(1);
        $chambre->setType('single');
        $chambre->setNombreLit(1);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();
        $id = $chambre->getId();

        $client->loginUser($this->admin);
        $crawler = $client->request('GET', '/admin/chambre/' . $id . '/edit');
        $form = $crawler->selectButton('Update')->form([
            'chambre[etage]' => '5',
            'chambre[type]' => 'double',
            'chambre[nombreLit]' => '2',
            'chambre[hotel]' => $this->hotel->getId(),
        ]);
        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $updatedChambre = $entityManager->getRepository(Chambre::class)->find($id);
        self::assertSame(5, $updatedChambre->getEtage());
    }

    public function testDeleteWithValidCsrfToken(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(1);
        $chambre->setType('single');
        $chambre->setNombreLit(1);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();
        $id = $chambre->getId();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/chambre/' . $id);
        $form = $client->getCrawler()->selectButton('Delete')->form();

        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $deletedChambre = $entityManager->getRepository(Chambre::class)->find($id);
        self::assertNull($deletedChambre);
    }
}
