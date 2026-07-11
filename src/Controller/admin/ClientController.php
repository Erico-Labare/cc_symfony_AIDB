<?php

namespace App\Controller\admin;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/client')]
#[IsGranted('ROLE_ADMIN')]
final class ClientController extends AbstractController
{
    // Lister tous les clients avec pagination et recherche
    #[Route('/', name: 'app_admin_client_index', methods: ['GET'])]
    public function index(Request $request, ClientRepository $clientRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');

        $clients = $clientRepository->paginateClients($page, $limit, $search);
        $maxPages = ceil(count($clients) / $limit);

        return $this->render('admin/client/index.html.twig', [
            'clients' => $clients,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_admin_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('admin/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
    }
}
