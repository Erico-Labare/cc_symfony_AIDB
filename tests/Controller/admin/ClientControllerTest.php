<?php

namespace App\Tests\Controller\admin;

use App\Entity\Client;
use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test du contrôleur de gestion des clients
final class ClientControllerTest extends WebTestCase
{
    private ?Compte $admin = null;

    // Initialiser les données de test
    private function setupData(): void
    {
        if ($this->admin === null) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $this->admin = $entityManager->getRepository(Compte::class)
                ->findOneBy(['email' => 'admin@test.com']);
        }
    }

    // Tester l'accès à l'index sans authentification
    public function testIndexWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/client');
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
        $client->request('GET', '/admin/client');
        self::assertResponseStatusCodeSame(403);
    }

    // Tester l'accès à l'index avec un admin
    public function testIndexWithAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/client');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester l'affichage du formulaire de création
    public function testNewFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/client/new');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester la soumission du formulaire de création
    public function testNewFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);

        $crawler = $client->request('GET', '/admin/client/new');
        $form = $crawler->selectButton('Enregistrer')->form([
            'client[nom]' => 'Client Test ' . time(),
            'client[adresse]' => '123 Rue du Client',
            'client[email]' => 'client' . time() . '@test.com',
            'client[telephone]' => '0123456789',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/client/');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $clients = $entityManager->getRepository(Client::class)
            ->findAll();
        self::assertGreaterThanOrEqual(1, count($clients));
    }

    // Tester l'affichage d'un client existant
    public function testShowExistingClient(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $testClient->setNom('Show Client');
        $testClient->setAdresse('Adresse Show');
        $testClient->setEmail('show' . time() . '@test.com');
        $testClient->setTelephone('0987654321');
        $entityManager->persist($testClient);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/client/' . $testClient->getId());
        self::assertResponseStatusCodeSame(200);
    }

    // Tester l'affichage d'un client inexistant
    public function testShowNonExistentClient(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/client/99999');
        self::assertResponseStatusCodeSame(404);
    }

    // Tester l'affichage du formulaire de modification
    public function testEditFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $testClient->setNom('Edit Client Original');
        $testClient->setAdresse('Edit Adresse Original');
        $testClient->setEmail('edit_original' . time() . '@test.com');
        $testClient->setTelephone('0101010101');
        $entityManager->persist($testClient);
        $entityManager->flush();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/client/' . $testClient->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester la soumission du formulaire de modification
    public function testEditFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $testClient->setNom('Edit Client Original');
        $testClient->setAdresse('Edit Adresse Original');
        $testClient->setEmail('edit_submit' . time() . '@test.com');
        $testClient->setTelephone('0101010101');
        $entityManager->persist($testClient);
        $entityManager->flush();
        $id = $testClient->getId();

        $client->loginUser($this->admin);
        $crawler = $client->request('GET', '/admin/client/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form([
            'client[nom]' => 'Client Updated',
            'client[adresse]' => 'New Updated Address',
            'client[email]' => 'updated' . time() . '@test.com',
            'client[telephone]' => '0606060606',
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/admin/client/');

        $entityManager->clear();
        $updatedClient = $entityManager->getRepository(Client::class)->find($id);
        self::assertSame('Client Updated', $updatedClient->getNom());
        self::assertSame('New Updated Address', $updatedClient->getAdresse());
    }

    // Tester la suppression d'un client avec un jeton CSRF valide
    public function testDeleteWithValidCsrfToken(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $testClient = new Client();
        $testClient->setNom('Client to Delete');
        $testClient->setAdresse('Delete Client Address');
        $testClient->setEmail('delete' . time() . '@test.com');
        $testClient->setTelephone('0707070707');
        $entityManager->persist($testClient);
        $entityManager->flush();
        $id = $testClient->getId();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/client/' . $id); // Naviguer vers la page show pour obtenir le formulaire de suppression
        $form = $client->getCrawler()->selectButton('Supprimer')->form();

        $client->submit($form);
        self::assertResponseRedirects('/admin/client/');

        $entityManager->clear();
        $deletedClient = $entityManager->getRepository(Client::class)->find($id);
        self::assertNull($deletedClient);
    }
}
