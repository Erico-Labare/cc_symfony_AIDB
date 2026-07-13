<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Hotel;
use App\Entity\Chambre;
use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures de données pour l'application en environnement de développement.
 *
 * Cette classe charge un ensemble de données de base (hôtels, chambres, clients,
 * comptes utilisateurs et réservations) pour faciliter le développement et les tests
 * en environnement non-production.
 */
class AppFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * Définit les groupes de fixtures auxquels cette classe appartient.
     *
     * Permet de charger sélectivement les fixtures. Ici, elles sont destinées
     * à l'environnement de développement.
     *
     * @return array<string> Un tableau de noms de groupes.
     */
    public static function getGroups(): array
    {
        return ['dev'];
    }

    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Constructeur de AppFixtures.
     *
     * Injecte le service de hachage des mots de passe.
     *
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Charge les données de fixtures dans la base de données.
     *
     * Cette méthode crée et persiste des entités Hotel, Chambre, Client, Compte
     * et Reservation pour peupler la base de données de développement.
     *
     * @param ObjectManager $manager Le gestionnaire d'objets Doctrine.
     */
    public function load(ObjectManager $manager): void
    {
        /*
        |--------------------------------------------------------------------------
        | HOTELS
        |--------------------------------------------------------------------------
        */

        $hotel1 = new Hotel();
        $hotel1->setNom('Hotel Toulouse Centre');
        $hotel1->setAdresse('10 Rue de la Paix, Toulouse');
        $hotel1->setCategorie('****');

        $manager->persist($hotel1);

        $hotel2 = new Hotel();
        $hotel2->setNom('Hotel Occitanie');
        $hotel2->setAdresse('25 Avenue Jean Jaures, Toulouse');
        $hotel2->setCategorie('***');

        $manager->persist($hotel2);

        /*
        |--------------------------------------------------------------------------
        | CHAMBRES
        |--------------------------------------------------------------------------
        */

        $chambre1 = new Chambre();
        $chambre1->setEtage(1);
        $chambre1->setType('Single');
        $chambre1->setNombreLit(1);
        $chambre1->setHotel($hotel1);

        $manager->persist($chambre1);

        $chambre2 = new Chambre();
        $chambre2->setEtage(2);
        $chambre2->setType('Double');
        $chambre2->setNombreLit(2);
        $chambre2->setHotel($hotel1);

        $manager->persist($chambre2);

        $chambre3 = new Chambre();
        $chambre3->setEtage(3);
        $chambre3->setType('Suite');
        $chambre3->setNombreLit(3);
        $chambre3->setHotel($hotel2);

        $manager->persist($chambre3);

        /*
        |--------------------------------------------------------------------------
        | CLIENTS
        |--------------------------------------------------------------------------
        */

        $client1 = new Client();
        $client1->setNom('Dupont');
        $client1->setAdresse('5 Rue Alsace Lorraine, Toulouse');
        $client1->setEmail('dupont@example.com');
        $client1->setTelephone('0601020304');

        $manager->persist($client1);

        $client2 = new Client();
        $client2->setNom('Martin');
        $client2->setAdresse('12 Boulevard Carnot, Toulouse');
        $client2->setEmail('martin@example.com');
        $client2->setTelephone('0605060708');

        $manager->persist($client2);

        /*
        |--------------------------------------------------------------------------
        | COMPTES
        |--------------------------------------------------------------------------
        */

        $compteAdmin = new Compte();
        $compteAdmin->setEmail('admin@hotel.fr');
        $compteAdmin->setRole('ROLE_ADMIN');
        $compteAdmin->setIsVerified(true); // Marquer comme vérifié pour les fixtures de dev

        $hashedPasswordAdmin = $this->passwordHasher->hashPassword(
            $compteAdmin,
            'admin123'
        );

        $compteAdmin->setPassword($hashedPasswordAdmin);

        $manager->persist($compteAdmin);

        $compteUser = new Compte();
        $compteUser->setEmail('user@hotel.fr');
        $compteUser->setRole('ROLE_USER');
        $compteUser->setIsVerified(true); // Marquer comme vérifié pour les fixtures de dev

        $hashedPasswordUser = $this->passwordHasher->hashPassword(
            $compteUser,
            'user123'
        );

        $compteUser->setPassword($hashedPasswordUser);

        $manager->persist($compteUser);

        /*
        |--------------------------------------------------------------------------
        | RESERVATIONS
        |--------------------------------------------------------------------------
        */

        $reservation1 = new Reservation();
        $reservation1->setDateDebut(new \DateTime('2026-06-01'));
        $reservation1->setDateFin(new \DateTime('2026-06-07'));
        $reservation1->setCommentaire('Demande de lit bébé.');
        $reservation1->setCompte($compteUser);
        $reservation1->setClient($client1);
        $reservation1->setChambre($chambre2);

        $manager->persist($reservation1);

        /*
        |--------------------------------------------------------------------------
        | ENREGISTREMENT FINAL EN BASE DE DONNEES
        |--------------------------------------------------------------------------
        */

        $manager->flush();
    }
}
