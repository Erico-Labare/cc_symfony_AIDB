<?php

namespace App\Controller\admin;

use App\Repository\ChambreRepository;
use App\Repository\ClientRepository;
use App\Repository\CompteRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(
        HotelRepository $hotelRepository,
        ChambreRepository $chambreRepository,
        ClientRepository $clientRepository,
        CompteRepository $compteRepository,
        ReservationRepository $reservationRepository
    ): Response {

        return $this->render('admin/index.html.twig', [

            'hotelCount' => $hotelRepository->count([]),

            'chambreCount' => $chambreRepository->count([]),

            'clientCount' => $clientRepository->count([]),

            'compteCount' => $compteRepository->count([]),

            'reservationCount' => $reservationRepository->count([]),

        ]);
    }
}