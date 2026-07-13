<?php

namespace App\DataFixtures;

use App\Entity\Compte;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures de données pour l'environnement de test.
 *
 * Cette classe charge un ensemble de comptes utilisateurs spécifiques
 * (utilisateur standard, utilisateur pour test d'enregistrement, administrateur)
 * pour être utilisés dans les tests fonctionnels et unitaires.
 */
class TestFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * Définit les groupes de fixtures auxquels cette classe appartient.
     *
     * Permet de charger sélectivement les fixtures. Ici, elles sont destinées
     * à l'environnement de test.
     *
     * @return array<string> Un tableau de noms de groupes.
     */
    public static function getGroups(): array
    {
        return ['test'];
    }

    /**
     * Constructeur de TestFixtures.
     *
     * Injecte le service de hachage des mots de passe.
     *
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     */
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Charge les données de fixtures dans la base de données de test.
     *
     * Cette méthode crée et persiste des entités Compte avec différents rôles
     * et emails pour simuler des scénarios d'authentification et d'autorisation.
     *
     * @param ObjectManager $manager Le gestionnaire d'objets Doctrine.
     */
    public function load(ObjectManager $manager): void
    {
        /*
        -------------------------
        USER 1 - LOGIN TEST
        -------------------------
        */

        $user = new Compte();
        $user->setEmail('test@test.com');
        $user->setRole('ROLE_USER');
        $user->setIsVerified(true); // Marquer comme vérifié pour les tests de connexion
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'password')
        );

        $manager->persist($user);

        /*
        --------------------------
        USER 2 - REGISTRATION TEST
        --------------------------
        */

        $user2 = new Compte();
        $user2->setEmail('email@example.com');
        $user2->setRole('ROLE_USER');
        $user2->setIsVerified(true); // Marquer comme vérifié pour les tests d'enregistrement
        $user2->setPassword(
            $this->passwordHasher->hashPassword($user2, 'password')
        );

        $manager->persist($user2);

        /*
        --------------------------
         USER 3 - ADMIN TEST
        --------------------------
        */

        $admin = new Compte();
        $admin->setEmail('admin@test.com');
        $admin->setRole('ROLE_ADMIN');
        $admin->setIsVerified(true); // Marquer comme vérifié pour les tests admin

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );

        $manager->persist($admin);

        $manager->flush();
    }
}
