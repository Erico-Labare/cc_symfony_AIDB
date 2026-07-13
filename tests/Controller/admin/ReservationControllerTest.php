<?php

namespace App\Tests\Controller\admin;

use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Entity\Chambre;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Test du contrôleur d'administration des réservations.
 *
 * Cette classe contient les tests fonctionnels pour la gestion des réservations
 * par un administrateur. Elle couvre les scénarios d'accès, de création,
 * de modification, de consultation et de suppression de réservations, en vérifiant
 * les autorisations nécessaires pour chaque action.
 */
final class ReservationControllerTest extends BaseWebTestCase
{
    private KernelBrowser $client;
    private ?Compte $admin = null;
    private ?Compte $nonAdminUser = null;
    private ?Client $testClient = null;
    private ?Chambre $testChambre = null;
    private ?Compte $testCompte = null;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le client de test et prépare les données utilisateur (admin et non-admin),
     * ainsi que les entités nécessaires pour les réservations (client, chambre, compte).
     * Cette méthode est appelée avant chaque exécution de test pour s'assurer
     * d'un environnement propre et cohérent.
     */
    protected function setUp(): void
    {
        parent::setUp(); // Appelle le setUp parent pour gérer la réinitialisation de la base de données et le démarrage du kernel
        $this->client = static::createClient(); // Crée le client
        $this->setupData(); // Appelle setupData après que la base de données soit prête et le client créé
    }

    /**
     * Initialise les données de test, s'assurant qu'un utilisateur admin, un utilisateur non-admin,
     * un client, un compte et une chambre de test existent.
     *
     * Crée un utilisateur avec le rôle 'ROLE_ADMIN', un autre avec 'ROLE_USER',
     * ainsi qu'un client, un compte et une chambre de test s'ils n'existent pas déjà,
     * pour être utilisés dans les scénarios de test d'autorisation et de manipulation de données.
     */
    private function setupData(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Assure qu'un utilisateur admin existe ou le crée
        $this->admin = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'admin@test.com']);

        if ($this->admin === null) {
            $this->admin = new Compte();
            $this->admin->setEmail('admin@test.com');
            $this->admin->setPassword($passwordHasher->hashPassword($this->admin, 'password'));
            $this->admin->setRole('ROLE_ADMIN');
            $this->admin->setIsVerified(true);
            $entityManager->persist($this->admin);
        }

        // Assure qu'un utilisateur non-admin existe ou le crée
        $this->nonAdminUser = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        if ($this->nonAdminUser === null) {
            $this->nonAdminUser = new Compte();
            $this->nonAdminUser->setEmail('test@test.com');
            $this->nonAdminUser->setPassword($passwordHasher->hashPassword($this->nonAdminUser, 'password'));
            $this->nonAdminUser->setRole('ROLE_USER');
            $this->nonAdminUser->setIsVerified(true);
            $entityManager->persist($this->nonAdminUser);
        }

        // Crée des données de test uniques pour éviter les conflits entre les méthodes de test
        $uniqueId = uniqid();

        // Créer un client de test
        $this->testClient = new Client();
        $this->testClient->setNom('Test Client Resa ' . $uniqueId);
        $this->testClient->setAdresse('123 Rue Resa ' . $uniqueId);
        $this->testClient->setEmail('resa_client' . $uniqueId . '@test.com');
        $this->testClient->setTelephone('0102030405');
        $entityManager->persist($this->testClient);

        // Créer un compte de test
        $this->testCompte = new Compte();
        $this->testCompte->setEmail('resa_compte' . $uniqueId . '@test.com');
        $this->testCompte->setPassword($passwordHasher->hashPassword($this->testCompte, 'password'));
        $this->testCompte->setRole('ROLE_USER');
        $this->testCompte->setIsVerified(true);
        $entityManager->persist($this->testCompte);

        // Créer un hôtel de test
        $testHotel = new Hotel();
        $testHotel->setNom('Hotel Resa ' . $uniqueId);
        $testHotel->setAdresse('Adresse Resa ' . $uniqueId);
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
        $entityManager->clear(); // Efface l'EntityManager pour détacher les entités
    }

    /**
     * Teste l'accès à la liste des réservations sans authentification.
     *
     * Vérifie qu'un utilisateur non connecté est redirigé vers la page de connexion
     * lorsqu'il tente d'accéder à la liste des réservations.
     */
    public function testIndexWithoutAuthentication(): void
    {
        $this->client->request('GET', '/admin/reservation');
        self::assertResponseRedirects();
    }

    /**
     * Teste l'accès à la liste des réservations avec un utilisateur non-admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_USER' reçoit une erreur 403
     * (Accès interdit) lorsqu'il tente d'accéder à la liste des réservations.
     */
    public function testIndexWithNonAdmin(): void
    {
        $this->client->loginUser($this->nonAdminUser);
        $this->client->request('GET', '/admin/reservation');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * Teste l'accès à la liste des réservations avec un admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_ADMIN' peut accéder
     * à la liste des réservations avec succès (statut 200).
     */
    public function testIndexWithAdmin(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/reservation');
        $this->client->followRedirect(); // Suit la redirection 301
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage du formulaire de création d'une nouvelle réservation.
     *
     * Vérifie qu'un administrateur peut accéder au formulaire de création
     * d'une réservation avec succès (statut 200).
     */
    public function testNewFormGet(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/reservation/new');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de création d'une nouvelle réservation.
     *
     * Simule la soumission d'un formulaire de création de réservation avec des données valides.
     * Vérifie la redirection après soumission et la persistance de la réservation en base de données.
     */
    public function testNewFormSubmit(): void
    {
        $this->client->loginUser($this->admin);

        $dateDebut = (new \DateTime())->modify('+1 day');
        $dateFin = (new \DateTime())->modify('+3 days');

        // Accède au formulaire de création
        $crawler = $this->client->request('GET', '/admin/reservation/new');
        // Soumet le formulaire avec des données valides
        $form = $crawler->selectButton('Enregistrer')->form([
            'reservation[dateDebut]' => $dateDebut->format('Y-m-d'),
            'reservation[dateFin]' => $dateFin->format('Y-m-d'),
            'reservation[commentaire]' => 'Commentaire test ' . uniqid(),
            'reservation[compte]' => $this->testCompte->getId(),
            'reservation[client]' => $this->testClient->getId(),
            'reservation[chambre]' => $this->testChambre->getId(),
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/reservation/');

        // Vérifie que la réservation a été créée en consultant la base de données
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $reservations = $entityManager->getRepository(Reservation::class)
            ->findAll();
        self::assertGreaterThanOrEqual(1, count($reservations));
    }

    /**
     * Teste l'affichage des détails d'une réservation existante.
     *
     * Crée une réservation de test, puis vérifie qu'un administrateur peut accéder
     * à sa page de détails avec succès (statut 200).
     */
    public function testShowExistingReservation(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Re-fetch des entités gérées après clear() pour s'assurer qu'elles sont attachées à l'EntityManager
        $managedTestCompte = $entityManager->getRepository(Compte::class)->find($this->testCompte->getId());
        $managedTestClient = $entityManager->getRepository(Client::class)->find($this->testClient->getId());
        $managedTestChambre = $entityManager->getRepository(Chambre::class)->find($this->testChambre->getId());

        // Crée une réservation de test
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+5 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+7 days'));
        $testReservation->setCommentaire('Show Resa ' . uniqid());
        $testReservation->setCompte($managedTestCompte);
        $testReservation->setClient($managedTestClient);
        $testReservation->setChambre($managedTestChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/reservation/' . $testReservation->getId());
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage des détails d'une réservation inexistante.
     *
     * Vérifie qu'une tentative d'accès aux détails d'une réservation avec un ID
     * qui n'existe pas renvoie une erreur 404 (Non trouvé).
     */
    public function testShowNonExistentReservation(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/reservation/99999'); // ID qui n'existe probablement pas
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Teste l'affichage du formulaire de modification d'une réservation existante.
     *
     * Crée une réservation de test, puis vérifie qu'un administrateur peut accéder
     * à son formulaire de modification avec succès (statut 200).
     */
    public function testEditFormGet(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Re-fetch des entités gérées après clear()
        $managedTestCompte = $entityManager->getRepository(Compte::class)->find($this->testCompte->getId());
        $managedTestClient = $entityManager->getRepository(Client::class)->find($this->testClient->getId());
        $managedTestChambre = $entityManager->getRepository(Chambre::class)->find($this->testChambre->getId());

        // Crée une réservation de test
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+10 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+12 days'));
        $testReservation->setCommentaire('Edit Resa Original ' . uniqid());
        $testReservation->setCompte($managedTestCompte);
        $testReservation->setClient($managedTestClient);
        $testReservation->setChambre($managedTestChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/reservation/' . $testReservation->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de modification d'une réservation.
     *
     * Crée une réservation de test, simule la soumission de son formulaire de modification
     * avec de nouvelles données. Vérifie la redirection après soumission et que
     * les données de la réservation ont été mises à jour en base de données.
     */
    public function testEditFormSubmit(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Re-fetch des entités gérées après clear()
        $managedTestCompte = $entityManager->getRepository(Compte::class)->find($this->testCompte->getId());
        $managedTestClient = $entityManager->getRepository(Client::class)->find($this->testClient->getId());
        $managedTestChambre = $entityManager->getRepository(Chambre::class)->find($this->testChambre->getId());

        // Crée une réservation de test
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+15 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+17 days'));
        $testReservation->setCommentaire('Edit Resa Original ' . uniqid());
        $testReservation->setCompte($managedTestCompte);
        $testReservation->setClient($managedTestClient);
        $testReservation->setChambre($managedTestChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();
        $id = $testReservation->getId();
        $entityManager->clear();

        $newDateDebut = (new \DateTime())->modify('+20 days');
        $newDateFin = (new \DateTime())->modify('+22 days');

        // Soumission du formulaire de modification
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/reservation/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form([
            'reservation[dateDebut]' => $newDateDebut->format('Y-m-d'),
            'reservation[dateFin]' => $newDateFin->format('Y-m-d'),
            'reservation[commentaire]' => 'Reservation Updated ' . uniqid(),
            'reservation[compte]' => $managedTestCompte->getId(),
            'reservation[client]' => $managedTestClient->getId(),
            'reservation[chambre]' => $managedTestChambre->getId(),
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/reservation/');

        // Vérifie que la réservation a été modifiée en le récupérant de la base de données
        $entityManager->clear();
        $updatedReservation = $entityManager->getRepository(Reservation::class)->find($id);
        self::assertNotNull($updatedReservation);
        self::assertStringContainsString('Reservation Updated', $updatedReservation->getCommentaire());
        self::assertEquals($newDateDebut->format('Y-m-d'), $updatedReservation->getDateDebut()->format('Y-m-d'));
    }

    /**
     * Teste la suppression d'une réservation avec un jeton CSRF valide.
     *
     * Crée une réservation de test, simule sa suppression via le formulaire de suppression
     * (qui inclut un jeton CSRF). Vérifie la redirection après suppression et que
     * la réservation n'existe plus en base de données.
     */
    public function testDeleteWithValidCsrfToken(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Re-fetch des entités gérées après clear()
        $managedTestCompte = $entityManager->getRepository(Compte::class)->find($this->testCompte->getId());
        $managedTestClient = $entityManager->getRepository(Client::class)->find($this->testClient->getId());
        $managedTestChambre = $entityManager->getRepository(Chambre::class)->find($this->testChambre->getId());

        // Crée une réservation de test
        $testReservation = new Reservation();
        $testReservation->setDateDebut((new \DateTime())->modify('+25 days'));
        $testReservation->setDateFin((new \DateTime())->modify('+27 days'));
        $testReservation->setCommentaire('Reservation to Delete ' . uniqid());
        $testReservation->setCompte($managedTestCompte);
        $testReservation->setClient($managedTestClient);
        $testReservation->setChambre($managedTestChambre);
        $entityManager->persist($testReservation);
        $entityManager->flush();
        $id = $testReservation->getId();
        $entityManager->clear();

        // Supprime la réservation via le formulaire
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/reservation/' . $id); // Navigue vers la page show pour obtenir le formulaire de suppression
        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/reservation/');

        // Vérifie que la réservation a été supprimée
        $entityManager->clear();
        $deletedReservation = $entityManager->getRepository(Reservation::class)->find($id);
        self::assertNull($deletedReservation);
    }
}
