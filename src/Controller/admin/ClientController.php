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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException; // Import added
use Doctrine\ORM\Exception\ORMException; // Import added

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
            try {
                $entityManager->persist($client);
                $entityManager->flush();
                $this->addFlash('success', 'Le client a été créé avec succès.');
                return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Un client avec cet e-mail existe déjà ou une autre contrainte unique a été violée.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du client : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('admin/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le client a été modifié avec succès.');
                return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Un client avec cet e-mail existe déjà ou une autre contrainte unique a été violée.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du client : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
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
            try {
                $entityManager->remove($client);
                $entityManager->flush();
                $this->addFlash('success', 'Le client a été supprimé avec succès.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du client : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
    }
}
