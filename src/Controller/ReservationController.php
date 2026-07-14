<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Reservation;
use App\Form\ReservationCommentType;
use App\Form\ReservationFormType;
use App\Repository\ClientRepository;
use App\Repository\ChambreRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use App\Services\DisponibiliteService;
use App\Services\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Exception\InvalidReservationDatesException;
use App\Exception\RoomUnavailableException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur gérant les actions liées aux réservations.
 *
 * Ce contrôleur permet aux utilisateurs de rechercher des chambres disponibles,
 * de créer, consulter, modifier (commentaire) et annuler leurs réservations.
 * Il intègre la logique de vérification de disponibilité et de gestion des erreurs.
 */
#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    /**
     * Affiche le formulaire de recherche de chambres disponibles et les résultats.
     *
     * Gère la soumission du formulaire de recherche, la récupération des critères
     * de recherche depuis la session (après une redirection, par exemple) et
     * l'affichage des chambres disponibles.
     *
     * @param Request $request La requête HTTP.
     * @param HotelRepository $hotelRepository Le dépôt des hôtels.
     * @param DisponibiliteService $disponibiliteService Le service de disponibilité des chambres.
     * @param SessionInterface $session La session HTTP pour stocker/récupérer les critères de recherche.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @param ClientRepository $clientRepository Le dépôt des clients.
     * @return Response Une réponse HTTP affichant le formulaire de recherche et les résultats.
     */
    #[Route('/search', name: 'app_reservation_search', methods: ['GET', 'POST'])]
    public function search(
        Request $request,
        HotelRepository $hotelRepository,
        DisponibiliteService $disponibiliteService,
        SessionInterface $session,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        ClientRepository $clientRepository
    ): Response {
        $formData = [];
        $availableRooms = [];

        // Check for stored search parameters in session (after login/registration redirect)
        if ($session->has('last_search_criteria')) {
            $storedCriteria = $session->get('last_search_criteria');
            $session->remove('last_search_criteria'); // Clear it after use

            if (isset($storedCriteria['hotel_id'])) {
                // If hotel_id was null, it should remain null for pre-filling
                $formData['hotel'] = $storedCriteria['hotel_id'] ? $hotelRepository->find($storedCriteria['hotel_id']) : null;
            }
            if (isset($storedCriteria['dateDebut'])) {
                $formData['dateDebut'] = new \DateTime($storedCriteria['dateDebut']);
            }
            if (isset($storedCriteria['dateFin'])) {
                $formData['dateFin'] = new \DateTime($storedCriteria['dateFin']);
            }
        }

        // If it's a POST request, try to get submitted data to pre-fill the form
        // This takes precedence over session data if a new search is performed
        if ($request->isMethod('POST')) {
            $submittedData = $request->request->all('reservation_form'); // Assuming 'reservation_form' is the form name

            // Pre-fill hotel
            if (isset($submittedData['hotel'])) {
                $formData['hotel'] = $submittedData['hotel'] ? $hotelRepository->find($submittedData['hotel']) : null;
            } else {
                $formData['hotel'] = null; // Ensure it's null if not submitted
            }

            // Pre-fill dateDebut
            if (isset($submittedData['dateDebut'])) {
                try {
                    $formData['dateDebut'] = new \DateTime($submittedData['dateDebut']);
                } catch (\Exception $e) {
                    $logger->warning('Invalid dateDebut format in search form: ' . $e->getMessage());
                    // No flash message here, as it's a pre-fill attempt, not a user-submitted error
                }
            }

            // Pre-fill dateFin
            if (isset($submittedData['dateFin'])) {
                try {
                    $formData['dateFin'] = new \DateTime($submittedData['dateFin']);
                } catch (\Exception $e) {
                    $logger->warning('Invalid dateFin format in search form: ' . $e->getMessage());
                    // No flash message here
                }
            }
        }

        $form = $this->createForm(ReservationFormType::class, $formData);
        $form->handleRequest($request);

        $availableRooms = [];
        $hotels = $hotelRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $hotel = $form->get('hotel')->getData();
            $dateDebut = $form->get('dateDebut')->getData();
            $dateFin = $form->get('dateFin')->getData();
            $hotelId = $hotel?->getId(); // This will be null if "Tous les Hôtels" is selected

            // Modified condition: allow hotelId to be null
            if ($dateDebut && $dateFin) {
                try {
                    // Pass hotelId (which can be null) to the service
                    $availableRooms = $disponibiliteService->findAvailableRooms($dateDebut, $dateFin, $hotelId);

                    // Store search criteria in session if user is not logged in
                    if (!$this->getUser()) {
                        $session->set('last_search_criteria', [
                            'hotel_id' => $hotelId, // Store null if no hotel selected
                            'dateDebut' => $dateDebut->format('Y-m-d'),
                            'dateFin' => $dateFin->format('Y-m-d'),
                        ]);
                    }

                } catch (InvalidReservationDatesException $e) { // Catch custom exception
                    $logger->warning('Invalid reservation dates in search: ' . $e->getMessage());
                    $translationKey = $e->getTranslationKey() ?? 'reservation.search.error.invalid_dates';
                    $translationParams = $e->getTranslationParameters();
                    $this->addFlash('error', $translator->trans($translationKey, $translationParams, 'app'));
                } catch (\InvalidArgumentException $e) {
                    $logger->warning('Invalid argument in search: ' . $e->getMessage());
                    $this->addFlash('error', $translator->trans('reservation.search.error.generic_invalid_argument', [], 'app'));
                }
            }
        }
        $clients = [];

        if ($this->getUser() instanceof Compte) {
            /** @var Compte $compteUser */
            $compteUser = $this->getUser();
            $clients = $clientRepository->findByCompte($compteUser);
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form,
            'availableRooms' => $availableRooms,
            'hotels' => $hotels,
            'clients' => $clients,
        ]);
    }

    /**
     * Crée une nouvelle réservation.
     *
     * Cette action est accessible via une requête POST après la sélection d'une chambre.
     * Elle gère la création d'un nouveau client si nécessaire, puis la création
     * de la réservation via le service dédié.
     *
     * @param Request $request La requête HTTP.
     * @param ReservationService $reservationService Le service de gestion des réservations.
     * @param ClientRepository $clientRepository Le dépôt des clients.
     * @param ChambreRepository $chambreRepository Le dépôt des chambres.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après la création ou en cas d'erreur.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas connecté.
     */
    #[Route('/create', name: 'app_reservation_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        ReservationService $reservationService,
        ClientRepository $clientRepository,
        ChambreRepository $chambreRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ): Response {
        $compte = $this->getUser();

        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException($translator->trans('access_denied.not_connected', [], 'app'));
        }

        $chambreId = $request->request->getInt('chambre_id');
        $dateDebut = $request->request->get('dateDebut');
        $dateFin = $request->request->get('dateFin');
        $commentaire = $request->request->get('commentaire');

        $clientId = $request->request->get('client_id');

        $nom = trim($request->request->get('client_nom'));
        $email = trim($request->request->get('client_email'));
        $telephone = trim($request->request->get('client_telephone'));
        $adresse = trim($request->request->get('client_adresse'));

        try {

            /*
            |--------------------------------------------------------------------------
            | Vérification de la chambre
            |--------------------------------------------------------------------------
            */

            $chambre = $chambreRepository->find($chambreId);

            if (!$chambre) {
                throw new \Exception($translator->trans('reservation.create.error.room_not_found_generic', [], 'app'));
            }

            /*
            |--------------------------------------------------------------------------
            | Gestion du client
            |--------------------------------------------------------------------------
            */

            if ($clientId) {

                // L'utilisateur a sélectionné un client existant.
                $client = $clientRepository->find($clientId);

                if (!$client) {
                    throw new \Exception($translator->trans('reservation.create.error.client_not_found', [], 'app'));
                }

                // Mise à jour éventuelle des informations.
                $client->setNom($nom);
                $client->setEmail($email);
                $client->setTelephone($telephone);
                $client->setAdresse($adresse);

            } else {

                // Aucun client sélectionné.
                // On vérifie qu'un client avec cet email n'existe pas déjà.

                $existingClient = $clientRepository->findClientForCompteByEmail(
                $compte,
                $email
                );

                if ($existingClient) {
                    throw new \Exception(
                        $translator->trans('reservation.create.error.client_email_exists', [], 'app')
                    );
                }

                // Création d'un nouveau client.
                $client = new Client();

                $client->setNom($nom);
                $client->setEmail($email);
                $client->setTelephone($telephone);
                $client->setAdresse($adresse);

                $entityManager->persist($client);
            }

            // Sauvegarde des modifications du client.
            $entityManager->flush();

            /*
            |--------------------------------------------------------------------------
            | Création de la réservation
            |--------------------------------------------------------------------------
            */

            $reservationService->createReservation(
                $chambre,
                $client,
                $compte,
                new \DateTime($dateDebut),
                new \DateTime($dateFin),
                $commentaire,
            );

            $this->addFlash('success', $translator->trans('reservation.create.success', [], 'app'));

            return $this->redirectToRoute('app_reservation_my_reservations');
        } catch (NotFoundHttpException $e) {
            $logger->warning('Reservation creation failed: ' . $e->getMessage());
            $this->addFlash('error', $translator->trans('reservation.create.error.room_not_found', [], 'app'));
            return $this->redirectToRoute('app_reservation_search');
        } catch (UniqueConstraintViolationException $e) {
            $logger->error('Reservation creation failed due to unique constraint violation: ' . $e->getMessage());
            $this->addFlash('error', $translator->trans('reservation.create.error.unique_constraint', [], 'app'));
            return $this->redirectToRoute('app_reservation_search');
        } catch (ORMException $e) {
            $logger->error('Reservation creation failed due to ORM exception: ' . $e->getMessage());
            $this->addFlash('error', $translator->trans('reservation.create.error.orm_exception', [], 'app'));
            return $this->redirectToRoute('app_reservation_search');
        } catch (InvalidReservationDatesException $e) {
            $logger->warning('Reservation creation failed due to invalid dates: ' . $e->getMessage());
            $translationKey = $e->getTranslationKey() ?? 'reservation.create.error.invalid_dates';
            $translationParams = $e->getTranslationParameters();
            $this->addFlash('error', $translator->trans($translationKey, $translationParams, 'app'));
            return $this->redirectToRoute('app_reservation_search');
        } catch (RoomUnavailableException $e) {
            $logger->warning('Reservation creation failed because room is unavailable: ' . $e->getMessage());
            $translationKey = $e->getTranslationKey() ?? 'reservation.create.error.room_unavailable';
            $translationParams = $e->getTranslationParameters();
            $this->addFlash('error', $translator->trans($translationKey, $translationParams, 'app'));
            return $this->redirectToRoute('app_reservation_search');
        } catch (\InvalidArgumentException $e) {
            $logger->warning('Reservation creation failed due to invalid argument: ' . $e->getMessage());
            $this->addFlash('error', $translator->trans('reservation.create.error.invalid_data', [], 'app'));
            return $this->redirectToRoute('app_reservation_search');
        } catch (\Exception $e) {
            $logger->critical('Unexpected error during reservation creation: ' . $e->getMessage());
            $this->addFlash('error', $translator->trans('reservation.create.error.unexpected', [], 'app'));
            return $this->redirectToRoute('app_reservation_search');
        }
    }

    /**
     * Affiche la liste des réservations de l'utilisateur connecté.
     *
     * @param TranslatorInterface $translator Le service de traduction.
     * @param ReservationRepository $reservationRepository Le dépôt des réservations. // Added this line
     * @return Response Une réponse HTTP affichant la liste des réservations.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas connecté.
     */
    #[Route('/my-reservations', name: 'app_reservation_my_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myReservations(
        TranslatorInterface $translator,
        ReservationRepository $reservationRepository // Added this line
    ): Response {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException($translator->trans('access_denied.not_connected', [], 'app'));
        }

        // Fetch reservations for the current account, ordered by ID descending
        $reservations = $reservationRepository->findBy(
            ['compte' => $compte],
            ['id' => 'DESC'] // Order by ID descending
        );

        return $this->render('reservation/my-reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Modifie le commentaire d'une réservation existante.
     *
     * Permet à l'utilisateur de mettre à jour le commentaire d'une de ses réservations.
     *
     * @param Request $request La requête HTTP.
     * @param Reservation $reservation L'entité Reservation à modifier (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant après succès.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas connecté ou n'est pas le propriétaire de la réservation.
     */
    #[Route('/{id}/comment/edit', name: 'app_reservation_comment_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editComment(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException($translator->trans('access_denied.not_connected', [], 'app'));
        }

        if ($reservation->getCompte() !== $compte) {
            throw $this->createAccessDeniedException($translator->trans('reservation.edit_comment.access_denied', [], 'app'));
        }

        $form = $this->createForm(ReservationCommentType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('reservation.edit_comment.success', [], 'app'));

            return $this->redirectToRoute('app_reservation_my_reservations');
        }

        return $this->render('reservation/edit-comment.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Annule une réservation.
     *
     * Supprime une réservation après vérification du jeton CSRF et des droits de l'utilisateur.
     *
     * @param Request $request La requête HTTP.
     * @param Reservation $reservation L'entité Reservation à annuler (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après l'annulation ou en cas d'erreur.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas connecté ou n'est pas le propriétaire de la réservation.
     */
    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ): Response {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException($translator->trans('access_denied.not_connected', [], 'app'));
        }

        if ($reservation->getCompte() !== $compte) {
            throw $this->createAccessDeniedException($translator->trans('reservation.cancel.access_denied', [], 'app'));
        }

        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($reservation);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('reservation.cancel.success', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Reservation cancellation failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('reservation.cancel.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during reservation cancellation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('reservation.cancel.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_reservation_my_reservations');
    }
}
