<?php

namespace App\Tests\Controller\admin;

use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Entity\Chambre;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test du contrôleur de gestion des réservations
final class ReservationControllerTest extends WebTestCase
{
    private ?Compte $admin = null;
    private ?Client $testClient = null;
    private ?Chambre $testChambre = null;
    private ?Compte $testCompte = null;

    // Initialiser les données de test
    private function setupData(): void
    {
        if ($this->admin === null) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $this->admin = $entityManager->getRepository(Compte::class)
                ->findOneBy(['email' => 'admin@test.com']);

            // Créer un client de test
            $this->testClient = new Client();
            $this->testClient->setNom('Test Client Resa');
            $this->testClient->setAdresse('123 Rue Resa');
            $this->testClient->setEmail('resa_client' . time() . '@test.com');
            $this->testClient->setTelephone('0102030405');
            $entityManager->persist($this->testClient);

            // Créer un compte de test
            $this->testCompte = new Compte();
            $this->testCompte->setEmail('resa_compte' . time() . '@test.com');
            $this->testCompte->setPassword('password');
            $this->testCompte->setRole('ROLE_USER');
            $this->testCompte->setIsVerified(true);
            $entityManager->persist($this->testCompte);

            // Créer un hôtel de test
            $testHotel = new Hotel();
            $testHotel->setNom('Hotel Resa');
            $testHotel->setAdresse('Adresse Resa');
            $testHotel->setCategorie('***');
            $entityManager->persist($testHotel);

            // Créer une chambre de test
            $this->testChambre = new Chambre();
            $this->testChambre->setEtage(1);
            $this->testChambre->setType('double');
            $this->testChambre->setNombreLit(2);
            $this->testChambre->setHotel($testHotel);
            $entityManager->persist($this->testChambre);

            $entityManager->flush();
        }
    }

    // Tester l'accès à l'index sans authentification
    public function testIndexWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/reservation');
        self::assertResponseRedirects();
    }

    // Tester l'accès à l'index avec un utilisateur non-admin
    public function testIndexWithNonAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);
        $client->loginUser($user);
        $client->request('GET', '/admin/reservation');
        self::assertResponseStatusCodeSame(403);
    }

    // Tester l'accès à l'index avec un admin
    public function testIndexWithAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/reservation');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester l'affichage du formulaire de création
    public function testNewFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/reservation/new');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester la soumission du formulaire de création
    public function testNewFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);

        $dateDebut = (new \DateTime())->modify('+1 day');
        $dateFin = (new \DateTime())->modify('+3 days');

        $crawler = $client->request('GET', '/admin/reservation/new');
        $form = $crawler->selectButton('Enregistrer')->form([
            'reservation[dateDebut]' => $dateDebut->format('Y-m-d H:i:s'),
            'reservation[dateFin]' => $dateFin->format('Y-m-d H:i:s'),
            'reservation[commentaire]' => 'Commentaire test ' . time(),
            'reservation[compte]' => $this->testCompte->getId(),
            'reservation[client]' => $this->testClient->getId(),
            'reservation[chambre]' => $this->testChambre->getId(),
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/reservation/');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $reservations = $entityManager->getRepository(Reservation::class)
            ->findAll();
        self::assertGreaterThanOrEqual(1, count($reservations));
    }

    // Tester l'affichage d'une réservation existante
    public function testShowExistingReservation(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+5 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+7 days'));
        $testReservation->setCommentaire('Show Resa');
        $testReservation->setCompte($this->testCompte);
        $testReservation->setClient($this->testClient);
        $testReservation->setChambre($this->testChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/reservation/' . $testReservation->getId());
        self::assertResponseStatusCodeSame(200);
    }

    // Tester l'affichage d'une réservation inexistante
    public function testShowNonExistentReservation(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/reservation/99999');
        self::assertResponseStatusCodeSame(404);
    }

    // Tester l'affichage du formulaire de modification
    public function testEditFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+10 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+12 days'));
        $testReservation->setCommentaire('Edit Resa Original');
        $testReservation->setCompte($this->testCompte);
        $testReservation->setClient($this->testClient);
        $testReservation->setChambre($this->testChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/reservation/' . $testReservation->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester la soumission du formulaire de modification
    public function testEditFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+15 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+17 days'));
        $testReservation->setCommentaire('Edit Resa Original');
        $testReservation->setCompte($this->testCompte);
        $testReservation->setClient($this->testClient);
        $testReservation->setChambre($this->testChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();
        $id = $testReservation->getId();

        $newDateDebut = (new \DateTime())->modify('+20 days');
        $newDateFin = (new \DateTime())->modify('+22 days');

        $client->loginUser($this->admin);
        $crawler = $client->request('GET', '/admin/reservation/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form([
            'reservation[dateDebut]' => $newDateDebut->format('Y-m-d H:i:s'),
            'reservation[dateFin]' => $newDateFin->format('Y-m-d H:i:s'),
            'reservation[commentaire]' => 'Reservation Updated',
            'reservation[compte]' => $this->testCompte->getId(),
            'reservation[client]' => $this->testClient->getId(),
            'reservation[chambre]' => $this->testChambre->getId(),
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/reservation/');

        $entityManager->clear();
        $updatedReservation = $entityManager->getRepository(Reservation::class)->find($id);
        self::assertSame('Reservation Updated', $updatedReservation->getCommentaire());
        self::assertEquals($newDateDebut->format('Y-m-d H:i:s'), $updatedReservation->getDateDebut()->format('Y-m-d H:i:s'));
    }

    // Tester la suppression d'une réservation avec un jeton CSRF valide
    public function testDeleteWithValidCsrfToken(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+25 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+27 days'));
        $testReservation->setCommentaire('Reservation to Delete');
        $testReservation->setCompte($this->testCompte);
        $testReservation->setClient($this->testClient);
        $testReservation->setChambre($this->testChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();
        $id = $testReservation->getId();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/reservation/' . $id); // Naviguer vers la page show pour obtenir le formulaire de suppression
        $form = $client->getCrawler()->selectButton('Supprimer')->form();

        $client->submit($form);
        self::assertResponseRedirects('/admin/reservation/');

        $entityManager->clear();
        $deletedReservation = $entityManager->getRepository(Reservation::class)->find($id);
        self::assertNull($deletedReservation);
    }
}
