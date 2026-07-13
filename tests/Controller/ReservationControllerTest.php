<?php

namespace App\Tests\Controller;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test du contrôleur de réservation.
 *
 * Cette classe contient les tests fonctionnels pour les actions liées aux réservations,
 * y compris la recherche de chambres, la création de réservations et la gestion des accès.
 */
class ReservationControllerTest extends WebTestCase
{
    private Compte $testUser;
    private Hotel $testHotel;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Assure que le kernel est arrêté avant chaque test pour éviter les fuites d'état
     * et garantir l'indépendance des tests.
     */
    protected function setUp(): void
    {
        self::ensureKernelShutdown();
    }

    /**
     * Prépare les données de test nécessaires (client, utilisateur, hôtel, chambre).
     *
     * Cette méthode crée et persiste un hôtel, une chambre et un utilisateur de test
     * pour les besoins des scénarios de test.
     *
     * @return array Contient le client de test, l'utilisateur de test et l'hôtel de test.
     */
    protected function getTestData(): array
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Création d'un hôtel de test
        $hotel = new Hotel();
        $hotel->setNom('Hotel Test Reservation');
        $hotel->setAdresse('123 Rue Test');
        $hotel->setCategorie('***');
        $entityManager->persist($hotel);

        // Création d'une chambre de test
        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('double');
        $chambre->setNombreLit(2);
        $chambre->setHotel($hotel);
        $entityManager->persist($chambre);

        // Recherche ou création d'un utilisateur de test
        $testUser = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@test.com']);
        if (!$testUser) {
            $testUser = new Compte();
            $testUser->setEmail('test@test.com');
            $testUser->setPassword('hashed_password'); // Mot de passe haché pour le test
            $testUser->setRole('ROLE_USER');
            $entityManager->persist($testUser);
        }

        $entityManager->flush();

        return ['client' => $client, 'user' => $testUser, 'hotel' => $hotel];
    }

    /**
     * Teste l'accessibilité de la page de recherche de réservations.
     *
     * Vérifie que la page de recherche est accessible sans authentification
     * et qu'elle renvoie un statut HTTP 200 (OK).
     */
    public function testSearchPageAccessible(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $client->request('GET', '/reservation/search');

        self::assertResponseStatusCodeSame(200);
        self::assertStringContainsString('Rechercher une chambre', $client->getResponse()->getContent());
    }

    /**
     * Teste que la page de recherche s'affiche correctement sans dates spécifiées.
     *
     * Vérifie que la page de recherche est rendue avec succès même si aucun critère
     * de date n'est fourni, affichant ainsi le formulaire de recherche.
     */
    public function testSearchWithoutDatesShowsForm(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $client->request('GET', '/reservation/search');

        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste que l'accès à "Mes réservations" nécessite une authentification.
     *
     * Tente d'accéder à la page de création de réservation sans être connecté
     * et vérifie que l'utilisateur est redirigé vers la page de connexion.
     */
    public function testMyReservationsRequiresAuthentication(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = $entityManager->getRepository(Chambre::class)->findOneBy(['type' => 'double']);

        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-15 10:00',
            'dateFin' => '2026-06-20 10:00',
            'client_nom' => 'Test Client',
            'client_email' => 'client@example.com',
            'client_telephone' => '0123456789',
            'client_adresse' => '1 Test Address',
        ]);

        self::assertResponseRedirects('/login');
    }

    /**
     * Teste l'accès à la page "Mes réservations" avec authentification.
     *
     * Connecte un utilisateur de test et vérifie que la page "Mes réservations"
     * est accessible et affiche le titre attendu.
     */
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

    /**
     * Teste la création réussie d'une réservation.
     *
     * Connecte un utilisateur, soumet un formulaire de réservation valide,
     * vérifie la redirection vers la page "Mes réservations" et la persistance
     * de la nouvelle réservation en base de données.
     */
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
            'client_nom' => 'Test Client',
            'client_email' => 'client@example.com',
            'client_telephone' => '0123456789',
            'client_adresse' => '1 Test Address',
        ]);

        self::assertResponseRedirects('/reservation/my-reservations');

        $entityManager->clear(); // Clear the entity manager to ensure fresh data is fetched
        $reservations = $entityManager->getRepository(\App\Entity\Reservation::class)->findBy(['chambre' => $chambre]);
        self::assertGreaterThanOrEqual(1, count($reservations));
    }

    /**
     * Teste la tentative de création d'une réservation pour une chambre déjà indisponible.
     *
     * Crée une première réservation pour une chambre, puis tente d'en créer une seconde
     * qui chevauche les dates de la première. Vérifie la redirection vers la page de recherche
     * et la présence d'un message d'erreur indiquant l'indisponibilité de la chambre.
     */
    public function testCreateReservationForUnavailableRoom(): void
    {
        $data = $this->getTestData();
        $client = $data['client'];
        $testUser = $data['user'];

        $client->loginUser($testUser);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = $entityManager->getRepository(Chambre::class)->findOneBy(['type' => 'double']);

        // Première réservation pour rendre la chambre indisponible
        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-15 10:00',
            'dateFin' => '2026-06-20 10:00',
            'client_nom' => 'Test Client One',
            'client_email' => 'client1@example.com',
            'client_telephone' => '0123456781',
            'client_adresse' => '1 Test Address One',
        ]);

        // Deuxième réservation pour la même chambre, avec des dates qui se chevauchent
        $client->request('POST', '/reservation/create', [
            'chambre_id' => $chambre->getId(),
            'dateDebut' => '2026-06-18 10:00',
            'dateFin' => '2026-06-22 10:00',
            'client_nom' => 'Test Client Two',
            'client_email' => 'client2@example.com',
            'client_telephone' => '0123456782',
            'client_adresse' => '2 Test Address Two',
        ]);

        self::assertResponseRedirects('/reservation/search');
        $client->followRedirect(); // Suit la redirection vers la page de recherche
        self::assertStringContainsString('reservation.error.room_unavailable', $client->getResponse()->getContent()); // Vérifie le message d'erreur
    }
}
