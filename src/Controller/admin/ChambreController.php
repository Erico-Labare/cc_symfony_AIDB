<?php

namespace App\Controller\admin;

use App\Entity\Chambre;
use App\Form\ChambreType;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/chambre')]
#[IsGranted('ROLE_ADMIN')]
final class ChambreController extends AbstractController
{
    // Lister toutes les chambres avec pagination et recherche
    #[Route(name: 'app_admin_chambre_index', methods: ['GET'])]
    public function index(Request $request, ChambreRepository $chambreRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');

        $chambres = $chambreRepository->paginateChambres($page, $limit, $search);
        $maxPages = ceil(count($chambres) / $limit);

        return $this->render('admin/chambre/index.html.twig', [
            'chambres' => $chambres,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
        ]);
    }

    // Créer une nouvelle chambre
    #[Route('/new', name: 'app_admin_chambre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($chambre);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_chambre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/chambre/new.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    // Afficher une chambre spécifique
    #[Route('/{id}', name: 'app_admin_chambre_show', methods: ['GET'])]
    public function show(Chambre $chambre): Response
    {
        return $this->render('admin/chambre/show.html.twig', [
            'chambre' => $chambre,
        ]);
    }

    // Modifier une chambre existante
    #[Route('/{id}/edit', name: 'app_admin_chambre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_chambre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/chambre/edit.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    // Supprimer une chambre
    #[Route('/{id}', name: 'app_admin_chambre_delete', methods: ['POST'])]
    public function delete(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chambre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chambre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_chambre_index', [], Response::HTTP_SEE_OTHER);
    }
}
