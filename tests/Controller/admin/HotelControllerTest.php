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
 * par un administrateur. Elle couvre les scénarios d'accès, de création,
 * de modification, de consultation et de suppression d'hôtels, en vérifiant
 * les autorisations nécessaires pour chaque action.
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
     * Cette méthode est appelée avant chaque exécution de test pour s'assurer
     * d'un environnement propre et cohérent.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient(); // Crée le client en premier, ce qui démarre le kernel
        parent::setUp(); // Appelle le setUp parent après le démarrage du kernel
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
     * Teste l'accès à la liste des hôtels sans authentification.
     *
     * Vérifie qu'un utilisateur non connecté est redirigé vers la page de connexion
     * lorsqu'il tente d'accéder à la liste des hôtels.
     */
    public function testIndexWithoutAuthentication(): void
    {
        $this->client->request('GET', '/admin/hotel');
        self::assertResponseRedirects();
    }

    /**
     * Teste l'accès à la liste des hôtels avec un utilisateur non-admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_USER' reçoit une erreur 403
     * (Accès interdit) lorsqu'il tente d'accéder à la liste des hôtels.
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
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_ADMIN' peut accéder
     * à la liste des hôtels avec succès (statut 200).
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
     * Vérifie qu'un administrateur peut accéder au formulaire de création
     * d'un hôtel avec succès (statut 200).
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
     * Simule la soumission d'un formulaire de création d'hôtel avec des données valides.
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
     * Crée un hôtel de test, puis vérifie qu'un administrateur peut accéder
     * à sa page de détails avec succès (statut 200).
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
     * Vérifie qu'une tentative d'accès aux détails d'un hôtel avec un ID
     * qui n'existe pas renvoie une erreur 404 (Non trouvé).
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
     * Crée un hôtel de test, puis vérifie qu'un administrateur peut accéder
     * à son formulaire de modification avec succès (statut 200).
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
     * Crée un hôtel de test, simule la soumission de son formulaire de modification
     * avec de nouvelles données. Vérifie la redirection après soumission et que
     * les données de l'hôtel ont été mises à jour en base de données.
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
     * Crée un hôtel de test, simule sa suppression via le formulaire de suppression
     * (qui inclut un jeton CSRF). Vérifie la redirection après suppression et que
     * l'hôtel n'existe plus en base de données.
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
