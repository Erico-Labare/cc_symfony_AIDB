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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Contracts\Translation\TranslatorInterface; // Import TranslatorInterface
use Psr\Log\LoggerInterface; // Import LoggerInterface
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Import NotFoundHttpException

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
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($client);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.client.new.success', [], 'app'));
                return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin client creation failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin client creation failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin client creation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        // The Client entity is automatically resolved by Symfony's ParamConverter.
        // If no client is found for the given ID, a NotFoundHttpException will be thrown automatically,
        // resulting in a 404 response, which is what the test expects for a non-existent client.
        return $this->render('admin/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.client.edit.success', [], 'app'));
                return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin client edit failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin client edit failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin client edit: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($client);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.client.delete.success', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin client deletion failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.delete.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin client deletion: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.client.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_admin_client_index', [], Response::HTTP_SEE_OTHER);
    }
}
