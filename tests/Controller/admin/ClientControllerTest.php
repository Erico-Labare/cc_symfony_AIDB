<?php

namespace App\Tests\Controller\admin;

use App\Entity\Client;
use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Test du contrôleur d'administration des clients.
 *
 * Cette classe contient les tests fonctionnels pour la gestion des clients
 * par un administrateur. Elle couvre les scénarios d'accès, de création,
 * de modification, de consultation et de suppression de clients, en vérifiant
 * les autorisations nécessaires pour chaque action.
 */
final class ClientControllerTest extends BaseWebTestCase
{
    private ?Compte $admin = null;
    private ?Compte $nonAdminUser = null;
    private ?KernelBrowser $client = null;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le client de test et prépare les données utilisateur (admin et non-admin).
     * Cette méthode est appelée avant chaque exécution de test pour s'assurer
     * d'un environnement propre et cohérent.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient(); // Crée le client en premier, ce qui démarre le kernel
        $this->setupData();
    }

    /**
     * Initialise les données de test, s'assurant qu'un utilisateur admin et un utilisateur non-admin existent.
     *
     * Crée un utilisateur avec le rôle 'ROLE_ADMIN' et un autre avec 'ROLE_USER'
     * s'ils n'existent pas déjà, pour être utilisés dans les scénarios de test
     * d'autorisation.
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

        $entityManager->flush();
        $entityManager->clear();
    }

    /**
     * Teste l'accès à la liste des clients sans authentification.
     *
     * Vérifie qu'un utilisateur non connecté est redirigé vers la page de connexion
     * lorsqu'il tente d'accéder à la liste des clients.
     */
    public function testIndexWithoutAuthentication(): void
    {
        $this->client->request('GET', '/admin/client');
        self::assertResponseRedirects();
    }

    /**
     * Teste l'accès à la liste des clients avec un utilisateur non-admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_USER' reçoit une erreur 403
     * (Accès interdit) lorsqu'il tente d'accéder à la liste des clients.
     */
    public function testIndexWithNonAdmin(): void
    {
        $this->client->loginUser($this->nonAdminUser);
        $this->client->request('GET', '/admin/client');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * Teste l'accès à la liste des clients avec un admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_ADMIN' peut accéder
     * à la liste des clients avec succès (statut 200).
     */
    public function testIndexWithAdmin(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/client');
        $this->client->followRedirect(); // Suit la redirection
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage du formulaire de création d'un nouveau client.
     *
     * Vérifie qu'un administrateur peut accéder au formulaire de création
     * d'un client avec succès (statut 200).
     */
    public function testNewFormGet(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/client/new');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de création d'un nouveau client.
     *
     * Simule la soumission d'un formulaire de création de client avec des données valides.
     * Vérifie la redirection après soumission et la persistance du client en base de données.
     */
    public function testNewFormSubmit(): void
    {
        $this->client->loginUser($this->admin);

        $uniqueId = uniqid();
        // Accède au formulaire de création
        $crawler = $this->client->request('GET', '/admin/client/new');
        // Soumet le formulaire avec des données valides
        $form = $crawler->selectButton('Enregistrer')->form([
            'client[nom]' => 'Client Test ' . $uniqueId,
            'client[adresse]' => '123 Rue du Client ' . $uniqueId,
            'client[email]' => 'client' . $uniqueId . '@test.com',
            'client[telephone]' => '0123456789',
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/client/');

        // Vérifie que le client a été créé en consultant la base de données
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $clients = $entityManager->getRepository(Client::class)
            ->findAll();
        self::assertGreaterThanOrEqual(1, count($clients));
    }

    /**
     * Teste l'affichage des détails d'un client existant.
     *
     * Crée un client de test, puis vérifie qu'un administrateur peut accéder
     * à sa page de détails avec succès (statut 200).
     */
    public function testShowExistingClient(): void
    {
        // Créer un client de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $uniqueId = uniqid();
        $testClient->setNom('Show Client ' . $uniqueId);
        $testClient->setAdresse('Adresse Show ' . $uniqueId);
        $testClient->setEmail('show' . $uniqueId . '@test.com');
        $testClient->setTelephone('0987654321');
        $entityManager->persist($testClient);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/client/' . $testClient->getId());
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage des détails d'un client inexistant.
     *
     * Vérifie qu'une tentative d'accès aux détails d'un client avec un ID
     * qui n'existe pas renvoie une erreur 404 (Non trouvé).
     */
    public function testShowNonExistentClient(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/client/99999'); // ID qui n'existe probablement pas
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Teste l'affichage du formulaire de modification d'un client existant.
     *
     * Crée un client de test, puis vérifie qu'un administrateur peut accéder
     * à son formulaire de modification avec succès (statut 200).
     */
    public function testEditFormGet(): void
    {
        // Créer un client de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $uniqueId = uniqid();
        $testClient->setNom('Edit Client Original ' . $uniqueId);
        $testClient->setAdresse('Edit Adresse Original ' . $uniqueId);
        $testClient->setEmail('edit_original' . $uniqueId . '@test.com');
        $testClient->setTelephone('0101010101');
        $entityManager->persist($testClient);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/client/' . $testClient->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de modification d'un client.
     *
     * Crée un client de test, simule la soumission de son formulaire de modification
     * avec de nouvelles données. Vérifie la redirection après soumission et que
     * les données du client ont été mises à jour en base de données.
     */
    public function testEditFormSubmit(): void
    {
        // Créer un client de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $uniqueId = uniqid();
        $testClient->setNom('Edit Client Original ' . $uniqueId);
        $testClient->setAdresse('Edit Adresse Original ' . $uniqueId);
        $testClient->setEmail('edit_submit' . $uniqueId . '@test.com');
        $testClient->setTelephone('0101010101');
        $entityManager->persist($testClient);
        $entityManager->flush();
        $id = $testClient->getId();
        $entityManager->clear();

        // Soumission du formulaire de modification
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/client/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form([
            'client[nom]' => 'Client Updated ' . uniqid(),
            'client[adresse]' => 'New Updated Address ' . uniqid(),
            'client[email]' => 'updated' . uniqid() . '@test.com',
            'client[telephone]' => '0606060606',
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/client/');

        // Vérifie que le client a été modifié en le récupérant de la base de données
        $entityManager->clear(); // Efface l'EntityManager pour s'assurer de récupérer les dernières données
        $updatedClient = $entityManager->getRepository(Client::class)->find($id);
        self::assertNotNull($updatedClient);
        self::assertStringContainsString('Client Updated', $updatedClient->getNom());
        self::assertStringContainsString('New Updated Address', $updatedClient->getAdresse());
    }

    /**
     * Teste la suppression d'un client avec un jeton CSRF valide.
     *
     * Crée un client de test, simule sa suppression via le formulaire de suppression
     * (qui inclut un jeton CSRF). Vérifie la redirection après suppression et que
     * le client n'existe plus en base de données.
     */
    public function testDeleteWithValidCsrfToken(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $uniqueId = uniqid();
        $testClient->setNom('Client to Delete ' . $uniqueId);
        $testClient->setAdresse('Delete Client Address ' . $uniqueId);
        $testClient->setEmail('delete' . $uniqueId . '@test.com');
        $testClient->setTelephone('0707070707');
        $entityManager->persist($testClient);
        $entityManager->flush();
        $id = $testClient->getId();
        $entityManager->clear();

        // Supprime le client via le formulaire
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/client/' . $id); // Accède à la page du client pour obtenir le formulaire de suppression
        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/client/');

        // Vérifie que le client a été supprimé
        $entityManager->clear();
        $deletedClient = $entityManager->getRepository(Client::class)->find($id);
        self::assertNull($deletedClient);
    }
}
