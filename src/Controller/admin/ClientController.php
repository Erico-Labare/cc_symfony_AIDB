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
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Contrôleur d'administration pour la gestion des clients.
 *
 * Ce contrôleur permet aux administrateurs (ROLE_ADMIN) de lister, créer,
 * afficher, modifier et supprimer des clients. Il intègre la gestion des
 * erreurs et la journalisation.
 */
#[Route('/admin/client')]
#[IsGranted('ROLE_ADMIN')]
final class ClientController extends AbstractController
{
    /**
     * Liste tous les clients avec des options de pagination et de recherche.
     *
     * @param Request $request La requête HTTP, utilisée pour récupérer les paramètres de page et de recherche.
     * @param ClientRepository $clientRepository Le dépôt des clients pour l'accès aux données.
     * @return Response Une réponse HTTP affichant la liste des clients.
     */
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

    /**
     * Crée un nouveau client.
     *
     * Affiche le formulaire de création et gère sa soumission. En cas de succès,
     * le client est persisté en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
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

    /**
     * Affiche les détails d'un client spécifique.
     *
     * @param Client $client L'entité Client à afficher (résolue par le ParamConverter).
     * @return Response Une réponse HTTP affichant les détails du client.
     */
    #[Route('/{id}', name: 'app_admin_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        // L'entité Client est automatiquement résolue par le ParamConverter de Symfony.
        // Si aucun client n'est trouvé pour l'ID donné, une NotFoundHttpException sera levée automatiquement,
        // résultant en une réponse 404, ce qui est le comportement attendu pour un client inexistant.
        return $this->render('admin/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    /**
     * Modifie un client existant.
     *
     * Affiche le formulaire de modification et gère sa soumission. En cas de succès,
     * les modifications sont persistées en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param Client $client L'entité Client à modifier (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
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

    /**
     * Supprime un client.
     *
     * Gère la suppression d'un client après vérification du jeton CSRF.
     *
     * @param Request $request La requête HTTP.
     * @param Client $client L'entité Client à supprimer (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après la suppression ou en cas d'erreur.
     */
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
