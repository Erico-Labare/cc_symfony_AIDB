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

class FullDataFixture extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getGroups(): array
    {
        return ['full_data'];
    }

    public function load(ObjectManager $manager): void
    {
        // Hotels
        $hotel1 = new Hotel();
        $hotel1->setNom('Hôtel Océan Bleu');
        $hotel1->setAdresse('12 Quai de la Fosse, 44000 Nantes');
        $hotel1->setCategorie('****'); // Correction ici
        $manager->persist($hotel1);

        $hotel2 = new Hotel();
        $hotel2->setNom('Hôtel Les Jardins');
        $hotel2->setAdresse('8 Rue des Lilas, 35000 Rennes');
        $hotel2->setCategorie('***'); // Correction ici
        $manager->persist($hotel2);

        $hotel3 = new Hotel();
        $hotel3->setNom('Hôtel Panorama');
        $hotel3->setAdresse('25 Avenue Victor Hugo, 44500 La Baule');
        $hotel3->setCategorie('*****'); // Correction ici
        $manager->persist($hotel3);

        // Chambres
        $chambre1 = new Chambre();
        $chambre1->setEtage(1);
        $chambre1->setType('Simple');
        $chambre1->setNombreLit(1);
        $chambre1->setHotel($hotel2);
        $manager->persist($chambre1);

        $chambre2 = new Chambre();
        $chambre2->setEtage(1);
        $chambre2->setType('Double');
        $chambre2->setNombreLit(2);
        $chambre2->setHotel($hotel1);
        $manager->persist($chambre2);

        $chambre3 = new Chambre();
        $chambre3->setEtage(2);
        $chambre3->setType('Suite');
        $chambre3->setNombreLit(2);
        $chambre3->setHotel($hotel2);
        $manager->persist($chambre3);

        $chambre4 = new Chambre();
        $chambre4->setEtage(1);
        $chambre4->setType('Double');
        $chambre4->setNombreLit(2);
        $chambre4->setHotel($hotel2);
        $manager->persist($chambre4);

        $chambre5 = new Chambre();
        $chambre5->setEtage(2);
        $chambre5->setType('Familiale');
        $chambre5->setNombreLit(4);
        $chambre5->setHotel($hotel2);
        $manager->persist($chambre5);

        $chambre6 = new Chambre();
        $chambre6->setEtage(3);
        $chambre6->setType('Suite');
        $chambre6->setNombreLit(2);
        $chambre6->setHotel($hotel2);
        $manager->persist($chambre6);

        $chambre7 = new Chambre();
        $chambre7->setEtage(1);
        $chambre7->setType('Simple');
        $chambre7->setNombreLit(1);
        $chambre7->setHotel($hotel3);
        $manager->persist($chambre7);

        $chambre8 = new Chambre();
        $chambre8->setEtage(2);
        $chambre8->setType('Double');
        $chambre8->setNombreLit(2);
        $chambre8->setHotel($hotel3);
        $manager->persist($chambre8);

        $chambre9 = new Chambre();
        $chambre9->setEtage(3);
        $chambre9->setType('Suite');
        $chambre9->setNombreLit(2);
        $chambre9->setHotel($hotel3);
        $manager->persist($chambre9);

        $chambre10 = new Chambre();
        $chambre10->setEtage(4);
        $chambre10->setType('Présidentielle');
        $chambre10->setNombreLit(2);
        $chambre10->setHotel($hotel3);
        $manager->persist($chambre10);

        // Comptes
        $compte1 = new Compte();
        $compte1->setEmail('thomas.martin@gmail.com');
        $compte1->setRole('ROLE_USER');
        $compte1->setIsVerified(true);
        $compte1->setPassword($this->passwordHasher->hashPassword($compte1, 'martin123'));
        $manager->persist($compte1);

        $compte2 = new Compte();
        $compte2->setEmail('julie.bernard@gmail.com');
        $compte2->setRole('ROLE_USER');
        $compte2->setIsVerified(true);
        $compte2->setPassword($this->passwordHasher->hashPassword($compte2, 'bernard123'));
        $manager->persist($compte2);

        $compte3 = new Compte();
        $compte3->setEmail('admin@hotelplus.fr');
        $compte3->setRole('ROLE_ADMIN');
        $compte3->setIsVerified(true);
        $compte3->setPassword($this->passwordHasher->hashPassword($compte3, 'admin123'));
        $manager->persist($compte3);

        // Clients
        $client1 = new Client();
        $client1->setNom('Thomas Martin');
        $client1->setAdresse('18 Rue de Strasbourg, 44000 Nantes');
        $client1->setEmail('thomas.martin@gmail.com');
        $client1->setTelephone('0625841762');
        $manager->persist($client1);

        $client2 = new Client();
        $client2->setNom('Sophie Martin');
        $client2->setAdresse('18 Rue de Strasbourg, 44000 Nantes');
        $client2->setEmail('sophie.martin@gmail.com');
        $client2->setTelephone('0647283951');
        $manager->persist($client2);

        $client3 = new Client();
        $client3->setNom('Léa Martin');
        $client3->setAdresse('18 Rue de Strasbourg, 44000 Nantes');
        $client3->setEmail('lea.martin@gmail.com');
        $client3->setTelephone('0651824490');
        $manager->persist($client3);

        $client4 = new Client();
        $client4->setNom('Julie Bernard');
        $client4->setAdresse('7 Rue des Acacias, 35000 Rennes');
        $client4->setEmail('julie.bernard@gmail.com');
        $client4->setTelephone('0673512264');
        $manager->persist($client4);

        $client5 = new Client();
        $client5->setNom('Hugo Bernard');
        $client5->setAdresse('7 Rue des Acacias, 35000 Rennes');
        $client5->setEmail('hugo.bernard@gmail.com');
        $client5->setTelephone('0661754833');
        $manager->persist($client5);

        $client6 = new Client();
        $client6->setNom('Emma Bernard');
        $client6->setAdresse('7 Rue des Acacias, 35000 Rennes');
        $client6->setEmail('emma.bernard@gmail.com');
        $client6->setTelephone('0668342718');
        $manager->persist($client6);

        // Réservations
        $reservation1 = new Reservation();
        $reservation1->setDateDebut(new \DateTime('2026-09-12'));
        $reservation1->setDateFin(new \DateTime('2026-09-15'));
        $reservation1->setCommentaire('Installer au moins une ventilateur.');
        $reservation1->setCompte($compte1);
        $reservation1->setClient($client2);
        $reservation1->setChambre($chambre3);
        $manager->persist($reservation1);

        $reservation2 = new Reservation();
        $reservation2->setDateDebut(new \DateTime('2026-09-02'));
        $reservation2->setDateFin(new \DateTime('2026-09-05'));
        $reservation2->setCommentaire('');
        $reservation2->setCompte($compte1);
        $reservation2->setClient($client3);
        $reservation2->setChambre($chambre8);
        $manager->persist($reservation2);

        $reservation3 = new Reservation();
        $reservation3->setDateDebut(new \DateTime('2026-08-15'));
        $reservation3->setDateFin(new \DateTime('2026-08-18'));
        $reservation3->setCommentaire('Installer un lit bébé.');
        $reservation3->setCompte($compte2);
        $reservation3->setClient($client4);
        $reservation3->setChambre($chambre2);
        $manager->persist($reservation3);

        $reservation4 = new Reservation();
        $reservation4->setDateDebut(new \DateTime('2026-09-10'));
        $reservation4->setDateFin(new \DateTime('2026-09-13'));
        $reservation4->setCommentaire('Verifier pour la présence de poussière (allergie).');
        $reservation4->setCompte($compte2);
        $reservation4->setClient($client5);
        $reservation4->setChambre($chambre5);
        $manager->persist($reservation4);

        $reservation5 = new Reservation();
        $reservation5->setDateDebut(new \DateTime('2026-10-01'));
        $reservation5->setDateFin(new \DateTime('2026-10-04'));
        $reservation5->setCommentaire('I want a coca in the mini-fridge.');
        $reservation5->setCompte($compte2);
        $reservation5->setClient($client6);
        $reservation5->setChambre($chambre9);
        $manager->persist($reservation5);

        $reservation6 = new Reservation();
        $reservation6->setDateDebut(new \DateTime('2026-11-07'));
        $reservation6->setDateFin(new \DateTime('2026-11-10'));
        $reservation6->setCommentaire('');
        $reservation6->setCompte($compte2);
        $reservation6->setClient($client4);
        $reservation6->setChambre($chambre10);
        $manager->persist($reservation6);

        $manager->flush();
    }
}
