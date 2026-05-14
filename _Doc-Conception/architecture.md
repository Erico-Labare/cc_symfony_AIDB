### Plan d'architecture envisagé : 

/src
 ├── Controller/
 │    ├── SecurityController.php
            - function - login
            - function - logout
 │    ├── HomeController.php
            - function - accueil
            - function - recherche disponibilité
 │    ├── ReservationController.php
            - function - réserver une chambre
            - function - voir ses réservations
            - function - annuler réservation
            - function - modifier commentaire
 │    ├── ClientController.php
            - function - voir son profil
            - function - modifier son profil
 │    ├── RegistrationController.php
 │    ├── ResetPasswordController.php
 │    ├── Admin/
 │         ├── ChambreController.php
 │         │    - function - ajouter une chambre
 │         │    - function - modifier une chambre
 │         │    - function - supprimer une chambre
 │         │    - function - rechercher des chambres
 │         │    - function - paginer les chambres
 │         ├── HotelController.php
                - function - ajouter un hôtel
                - function - modifier un hôtel
                - function - supprimer un hôtel
                - function - rechercher des hôtels
                - function - paginer les hôtels
 │         ├── ReservationAdminController.php
 │         │    - function - voir les réservations clients
 │         │    - function - filtrer les réservations par numéro
 │         ├── ClientAdminController.php
 │         │    - function - rechercher des clients
 │         │    - function - filtrer les clients par nom/email
 │         ├── CompteAdminController.php
 │         │    - function - rechercher des comptes
 │         │    - function - filtrer les comptes par rôle
 │    ├── Entity/
 |    │    ├── Compte.php
 |    │    ├── Client.php
 |    │    ├── Chambre.php
 |    │    ├── Hotel.php
 |    │    ├── Reservation.php
 |    │    ├── ResetPasswordRequest.php
 ├── Repository/
 │    ├── ClientRepository.php
 │    ├── ChambreRepository.php
 │    ├── HotelRepository.php
 │    ├── ReservationRepository.php
 │    ├── CompteRepository.php
 │    ├── ResetPasswordRequestRepository.php
 ├── Service/
 │    ├── ReservationService.php
 │         - function - créer une réservation
 │    ├── DisponibiliteService.php
 │         - function - vérifier disponibilité chambre
 │    ├── ClientService.php
 ├── Form/
 │    ├── LoginFormType.php
 │    ├── RegistrationFormType.php
 │    ├── ResetPasswordFormType.php
 │    ├── ReservationType.php
 │    ├── ChambreType.php
 │    ├── ClientType.php
 │    ├── HotelType.php
 ├── EventSubscriber/
 │    ├── ExceptionSubscriber.php
 ├── DataFixtures/
 │    ├── AppFixtures.php
/templates
 ├── base.html.twig
 ├── partials/
 │    ├── header.html.twig
 │    ├── footer.html.twig
 ├── home/
 ├── security/
 ├── reservation/
 ├── client/
 ├── admin/
 │    ├── chambre/
 │    ├── hotel/
 │    ├── reservation/
 │    ├── client/
 │    ├── compte/
/config
 ├── packages/
    - security.yaml ?
 ├── routes/
/migrations/
/tests/
 ├── Controller/
   ├── SecurityControllerTest.php
   ├── ReservationControllerTest.php
   ├── Admin/
   |    ├──ChambreControllerTest.php

 ├── Service/
 |    ├── DisponibiliteServiceTest.php
 |    ├── ReservationServiceTest.php
 |    ├── ClientServiceTest.php
 ├── Repository/
 |    ├── ChambreRepositoryTest.php
 |    ├── ReservationRepositoryTest.php

