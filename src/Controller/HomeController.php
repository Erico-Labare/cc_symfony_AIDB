<?php

namespace App\Controller;

use App\Repository\ChambreRepository;
use App\Repository\ClientRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les pages d'accueil de l'application.
 */
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home')]
    public function index(
        HotelRepository $hotelRepository,
        ChambreRepository $chambreRepository,
        ReservationRepository $reservationRepository,
        ClientRepository $clientRepository,
    ): Response {

        return $this->render('home/index.html.twig', [

            'hotelCount' => $hotelRepository->count([]),

            'roomCount' => $chambreRepository->count([]),

            'reservationCount' => $reservationRepository->count([]),

            'clientCount' => $clientRepository->count([]),

        ]);
    }
}