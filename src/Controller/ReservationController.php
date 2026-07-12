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

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    /**
     * Affiche le formulaire de recherche de chambres disponibles.
     * GET /reservation/search
     */
    #[Route('/search', name: 'app_reservation_search', methods: ['GET', 'POST'])]
    public function search(
        Request $request,
        HotelRepository $hotelRepository,
        DisponibiliteService $disponibiliteService,
        SessionInterface $session,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ): Response {
        $formData = [];
        $availableRooms = [];

        // Check for stored search parameters in session (after login/registration redirect)
        if ($session->has('last_search_criteria')) {
            $storedCriteria = $session->get('last_search_criteria');
            $session->remove('last_search_criteria'); // Clear it after use

            if (isset($storedCriteria['hotel_id'])) {
                $hotel = $hotelRepository->find($storedCriteria['hotel_id']);
                if ($hotel) {
                    $formData['hotel'] = $hotel;
                }
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
                $hotel = $hotelRepository->find($submittedData['hotel']);
                if ($hotel) {
                    $formData['hotel'] = $hotel;
                }
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
            $hotelId = $hotel?->getId();

            if ($hotelId && $dateDebut && $dateFin) {
                try {
                    $availableRooms = $disponibiliteService->findAvailableRooms($dateDebut, $dateFin, $hotelId);

                    // Store search criteria in session if user is not logged in
                    if (!$this->getUser()) {
                        $session->set('last_search_criteria', [
                            'hotel_id' => $hotelId,
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
            $clients = $clientRepository->findByCompte($this->getUser());
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
     * POST /reservation/create
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
                throw new \Exception('Chambre non trouvée.');
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
                    throw new \Exception('Client introuvable.');
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
                        'Un client avec cette adresse email existe déjà. Veuillez le sélectionner dans la liste.'
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
     * Affiche les réservations de l'utilisateur connecté.
     * GET /my-reservations
     */
    #[Route('/my-reservations', name: 'app_reservation_my_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myReservations(TranslatorInterface $translator): Response
    {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException($translator->trans('access_denied.not_connected', [], 'app'));
        }

        $reservations = $compte->getReservations();

        return $this->render('reservation/my-reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Modifie le commentaire d'une réservation.
     * GET|POST /reservation/{id}/comment/edit
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
     * POST /reservation/{id}/cancel
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
