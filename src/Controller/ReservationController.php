<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface; // Import SessionInterface

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
        SessionInterface $session // Inject SessionInterface
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
                    // Log or handle invalid date format if necessary
                }
            }

            // Pre-fill dateFin
            if (isset($submittedData['dateFin'])) {
                try {
                    $formData['dateFin'] = new \DateTime($submittedData['dateFin']);
                } catch (\Exception $e) {
                    // Log or handle invalid date format if necessary
                }
            }
        }

        $form = $this->createForm(ReservationFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $hotel = $data['hotel'] ?? null;
            $dateDebut = $data['dateDebut'] ?? null;
            $dateFin = $data['dateFin'] ?? null;

            $hotelId = $hotel ? $hotel->getId() : null;

            if ($hotelId && $dateDebut && $dateFin) {
                try {
                    // Adjust dateDebut to the start of the day (00:00:00)
                    $dateDebut->setTime(0, 0, 0);
                    // Adjust dateFin to the end of the day (23:59:59)
                    $dateFin->setTime(23, 59, 59);

                    $availableRooms = $disponibiliteService->findAvailableRooms($dateDebut, $dateFin, $hotelId);

                    // Store search criteria in session if user is not logged in
                    if (!$this->getUser()) {
                        $session->set('last_search_criteria', [
                            'hotel_id' => $hotelId,
                            'dateDebut' => $dateDebut->format('Y-m-d'),
                            'dateFin' => $dateFin->format('Y-m-d'),
                        ]);
                    }

                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form,
            'availableRooms' => $availableRooms,
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
        ClientRepository $clientRepository, // Keep for potential future use, but not directly used for client creation here
        ChambreRepository $chambreRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $chambreId = $request->request->getInt('chambre_id');
        $dateDebut = $request->request->get('dateDebut');
        $dateFin = $request->request->get('dateFin');
        $commentaire = $request->request->get('commentaire');

        // Get client details from the request
        $clientNom = $request->request->get('client_nom');
        $clientEmail = $request->request->get('client_email');
        $clientTelephone = $request->request->get('client_telephone');
        $clientAdresse = $request->request->get('client_adresse');

        try {
            $chambre = $chambreRepository->find($chambreId);
            if (!$chambre) {
                throw new \Exception('Chambre non trouvée.');
            }

            // Create a new Client entity for this reservation
            $client = new Client();
            $client->setNom($clientNom);
            $client->setEmail($clientEmail);
            $client->setTelephone($clientTelephone);
            $client->setAdresse($clientAdresse);
            $entityManager->persist($client);
            // No flush here, it will be flushed with the reservation

            $dateDebutObj = new \DateTime($dateDebut);
            $dateFinObj = new \DateTime($dateFin);

            $reservation = $reservationService->createReservation(
                $chambre,
                $client, // Pass the newly created client
                $compte,
                $dateDebutObj,
                $dateFinObj,
                $commentaire,
            );

            $this->addFlash('success', 'Réservation créée avec succès !');

            return $this->redirectToRoute('app_reservation_my_reservations');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la réservation : ' . $e->getMessage());

            return $this->redirectToRoute('app_reservation_search');
        }
    }

    /**
     * Affiche les réservations de l'utilisateur connecté.
     * GET /my-reservations
     */
    #[Route('/my-reservations', name: 'app_reservation_my_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myReservations(): Response
    {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $reservations = $compte->getReservations();

        return $this->render('reservation/my-reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }
}
