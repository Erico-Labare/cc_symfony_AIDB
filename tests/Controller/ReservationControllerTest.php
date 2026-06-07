<?php

namespace App\Tests\Controller;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationControllerTest extends WebTestCase
{
    private Compte $testUser;
    private Hotel $testHotel;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
    }

    protected function getTestData(): array
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $hotel = new Hotel();
        $hotel->setNom('Hotel Test Reservation');
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $entityManager->persist($hotel);

        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $entityManager->persist($chambre);

        $testUser = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@test.com']);
        if (!$testUser) {
            $testUser = new Compte();
            $testUser->setEmail('test@test.com');
            $testUser->setPassword('hashed_password');
            $testUser->setRole('ROLE_USER');
            $entityManager->persist($testUser);
        }

        $entityManager->flush();

        return ['client' => $client, 'user' => $testUser, 'hotel' => $hotel];
    }

    public function testSearchPageAccessible(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $client->request('GET', '/reservation/search');

        self::assertResponseStatusCodeSame(200);
        self::assertStringContainsString('Rechercher une chambre', $client->getResponse()->getContent());
    }

    public function testSearchWithoutDatesShowsForm(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $client->request('GET', '/reservation/search');

        self::assertResponseStatusCodeSame(200);
    }

    public function testMyReservationsRequiresAuthentication(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $client->request('GET', '/reservation/my-reservations');

        self::assertResponseRedirects();
    }

    public function testMyReservationsWithAuthentication(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];
        $testUser = $data['user'];

        $client->loginUser($testUser);
        $client->request('GET', '/reservation/my-reservations');

        self::assertResponseStatusCodeSame(200);
        self::assertStringContainsString('Mes réservations', $client->getResponse()->getContent());
    }

    public function testCreateReservationRequiresAuthentication(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = $entityManager->getRepository(Chambre::class)->findOneBy(['type' => 'double']);

        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-15 10:00',
            'dateFin' => '2026-06-20 10:00',
        ]);

        self::assertResponseRedirects('/login');
    }

    public function testCreateReservationSuccess(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];
        $testUser = $data['user'];

        $client->loginUser($testUser);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = $entityManager->getRepository(Chambre::class)->findOneBy(['type' => 'double']);

        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-15 10:00',
            'dateFin' => '2026-06-20 10:00',
            'commentaire' => 'Test comment',
        ]);

        self::assertResponseRedirects('/reservation/my-reservations');

        $entityManager->clear();
        $reservations = $entityManager->getRepository(\App\Entity\Reservation::class)->findBy(['chambre' => $chambre]);
        self::assertGreaterThanOrEqual(1, count($reservations));
    }

    public function testCreateReservationForUnavailableRoom(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];
        $testUser = $data['user'];

        $client->loginUser($testUser);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = $entityManager->getRepository(Chambre::class)->findOneBy(['type' => 'double']);

        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-15 10:00',
            'dateFin' => '2026-06-20 10:00',
        ]);

        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-18 10:00',
            'dateFin' => '2026-06-22 10:00',
        ]);

        self::assertResponseRedirects('/reservation/search');
        $client->followRedirect();
        self::assertStringContainsString('Erreur', $client->getResponse()->getContent());
    }
}
