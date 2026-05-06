### Plan d'architecture envisagé : 

/src
 ├── Controller/
 │    ├── SecurityController.php
 │    ├── HomeController.php
 │    ├── ReservationController.php
 │    ├── ClientController.php
 │    ├── Admin/
 │         ├── ChambreController.php
 │         ├── HotelController.php
 │         ├── ReservationAdminController.php
 │         ├── ClientAdminController.php
 ├── Entity/
 │    ├── Compte.php
 │    ├── Client.php
 │    ├── Chambre.php
 │    ├── Hotel.php
 │    ├── Reservation.php
 ├── Repository/
 │    ├── ClientRepository.php
 │    ├── ChambreRepository.php
 │    ├── HotelRepository.php
 │    ├── ReservationRepository.php
 ├── Service/
 │    ├── ReservationService.php
 │    ├── DisponibiliteService.php
 ├── Form/
 │    ├── LoginFormType.php
 │    ├── RegistrationFormType.php
 │    ├── ReservationType.php
 │    ├── ChambreType.php
 │    ├── ClientType.php
 ├── Security/
 │    ├── LoginAuthenticator.php
 ├── EventSubscriber/
 │    ├── ExceptionSubscriber.php
/templates
 ├── base.html.twig
 ├── home/
 ├── security/
 ├── reservation/
 ├── client/
 ├── admin/
 │    ├── chambre/
 │    ├── hotel/
 │    ├── reservation/
 │    ├── client/
/config
 ├── packages/
 ├── routes/
/migrations/
/tests/

