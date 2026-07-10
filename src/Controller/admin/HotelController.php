<?php

namespace App\Controller\admin;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/hotel')]
#[IsGranted('ROLE_ADMIN')]
final class HotelController extends AbstractController
{
    // Lister tous les hôtels avec pagination et recherche
    #[Route(name: 'app_admin_hotel_index', methods: ['GET'])]
    public function index(Request $request, HotelRepository $hotelRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');

        $hotels = $hotelRepository->paginateHotels($page, $limit, $search);
        $maxPages = ceil(count($hotels) / $limit);

        return $this->render('admin/hotel/index.html.twig', [
            'hotels' => $hotels,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
        ]);
    }

    // Créer un nouvel hôtel
    #[Route('/new', name: 'app_admin_hotel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hotel);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    // Afficher un hôtel spécifique
    #[Route('/{id}', name: 'app_admin_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel): Response
    {
        return $this->render('admin/hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    // Modifier un hôtel existant
    #[Route('/{id}/edit', name: 'app_admin_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    // Supprimer un hôtel
    #[Route('/{id}', name: 'app_admin_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $hotel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
