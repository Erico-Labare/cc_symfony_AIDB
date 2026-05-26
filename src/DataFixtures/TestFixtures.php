<?php

namespace App\DataFixtures;

use App\Entity\Compte;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture implements FixtureGroupInterface
{

    /**
     * Permet de faire al séparation entre les fixture de développement et les fixtures de test.
     */
    public static function getGroups(): array
    {
        return ['test'];
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

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

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );

        $manager->persist($admin);

        $manager->flush();

    }
}