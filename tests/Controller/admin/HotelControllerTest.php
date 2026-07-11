<?php

namespace App\Tests\Controller\admin;

use App\Entity\Hotel;
use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Test du contrôleur d'administration des hôtels.
 *
 * Cette classe contient les tests fonctionnels pour la gestion des hôtels
 * par un administrateur.
 */
final class HotelControllerTest extends BaseWebTestCase
{
    private KernelBrowser $client;
    private ?Compte $admin = null;
    private ?Compte $nonAdminUser = null;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le client de test et prépare les données utilisateur (admin et non-admin).
     */
    protected function setUp(): void
    {
        $this->client = static::createClient(); // Crée le client en premier, ce qui démarre le kernel
        parent::setUp(); // Appelle le setUp parent après le démarrage du kernel
        $this->setupData();
    }

    /**
     * Initialise les données de test, s'assurant qu'un utilisateur admin et un utilisateur non-admin existent.
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
     * Teste l'accès à la liste des hôtels sans authentification.
     *
     * Doit rediriger vers la page de connexion.
     */
    public function testIndexWithoutAuthentication(): void
    {
        $this->client->request('GET', '/admin/hotel');
        self::assertResponseRedirects();
    }

    /**
     * Teste l'accès à la liste des hôtels avec un utilisateur non-admin.
     *
     * Doit retourner un statut 403 (Accès interdit).
     */
    public function testIndexWithNonAdmin(): void
    {
        $this->client->loginUser($this->nonAdminUser);
        $this->client->request('GET', '/admin/hotel');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * Teste l'accès à la liste des hôtels avec un utilisateur admin.
     *
     * Doit retourner un statut 200 (Succès).
     */
    public function testIndexWithAdmin(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/hotel');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage du formulaire de création d'un nouvel hôtel.
     *
     * Doit retourner un statut 200 (Succès) pour un admin.
     */
    public function testNewFormGet(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/hotel/new');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de création d'un nouvel hôtel.
     *
     * Vérifie la redirection après soumission et la persistance de l'hôtel en base de données.
     */
    public function testNewFormSubmit(): void
    {
        $this->client->loginUser($this->admin);

        // Accède au formulaire de création
        $crawler = $this->client->request('GET', '/admin/hotel/new');
        // Soumet le formulaire avec des données valides
        $form = $crawler->selectButton('Enregistrer')->form([
            'hotel[nom]' => 'Hotel Luxe ' . uniqid(),
            'hotel[adresse]' => '45 Avenue des Champs, Paris ' . uniqid(),
            'hotel[categorie]' => '*****',
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/hotel');

        // Vérifie que l'hôtel a été créé en consultant la base de données
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotels = $entityManager->getRepository(Hotel::class)
            ->findAll();
        self::assertGreaterThanOrEqual(1, count($hotels));
    }

    /**
     * Teste l'affichage des détails d'un hôtel existant.
     *
     * Doit retourner un statut 200 (Succès) pour un admin.
     */
    public function testShowExistingHotel(): void
    {
        // Créer un hôtel de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test ' . uniqid());
        $hotel->setAdresse('123 Rue Test ' . uniqid());
        $hotel->setCategorie('***');
        $entityManager->persist($hotel);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/hotel/' . $hotel->getId());
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage des détails d'un hôtel inexistant.
     *
     * Doit retourner un statut 404 (Non trouvé).
     */
    public function testShowNonExistentHotel(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/hotel/99999'); // ID qui n'existe probablement pas
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Teste l'affichage du formulaire de modification d'un hôtel existant.
     *
     * Doit retourner un statut 200 (Succès) pour un admin.
     */
    public function testEditFormGet(): void
    {
        // Créer un hôtel de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel Original ' . uniqid());
        $hotel->setAdresse('Original Address ' . uniqid());
        $hotel->setCategorie('**');
        $entityManager->persist($hotel);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/hotel/' . $hotel->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de modification d'un hôtel.
     *
     * Vérifie la redirection après soumission et que les données de l'hôtel ont été mises à jour.
     */
    public function testEditFormSubmit(): void
    {
        // Créer un hôtel de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel Original ' . uniqid());
        $hotel->setAdresse('Original Address ' . uniqid());
        $hotel->setCategorie('**');
        $entityManager->persist($hotel);
        $entityManager->flush();
        $id = $hotel->getId();
        $entityManager->clear();

        // Soumission du formulaire de modification
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/hotel/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form([
            'hotel[nom]' => 'Hotel Updated ' . uniqid(),
            'hotel[adresse]' => 'New Address ' . uniqid(),
            'hotel[categorie]' => '****',
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/hotel');

        // Vérifie que l'hôtel a été modifié en le récupérant de la base de données
        $entityManager->clear(); // Efface l'EntityManager pour s'assurer de récupérer les dernières données
        $updatedHotel = $entityManager->getRepository(Hotel::class)->find($id);
        self::assertNotNull($updatedHotel);
        self::assertStringContainsString('Hotel Updated', $updatedHotel->getNom());
    }

    /**
     * Teste la suppression d'un hôtel avec un jeton CSRF valide.
     *
     * Crée un hôtel, le supprime via le formulaire et vérifie qu'il n'existe plus en base de données.
     */
    public function testDeleteWithValidCsrfToken(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $hotel = new Hotel();
        $hotel->setNom('Hotel to Delete ' . uniqid());
        $hotel->setAdresse('Delete Me Street ' . uniqid());
        $hotel->setCategorie('*');
        $entityManager->persist($hotel);
        $entityManager->flush();
        $id = $hotel->getId();
        $entityManager->clear();

        // Supprime l'hôtel via le formulaire
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/hotel/' . $id); // Accède à la page de l'hôtel pour obtenir le formulaire de suppression
        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/hotel');

        // Vérifie que l'hôtel a été supprimé
        $entityManager->clear();
        $deletedHotel = $entityManager->getRepository(Hotel::class)->find($id);
        self::assertNull($deletedHotel);
    }
}
