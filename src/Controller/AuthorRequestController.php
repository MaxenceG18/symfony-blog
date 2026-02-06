<?php

namespace App\Controller;

use App\Entity\AuthorRequest;
use App\Repository\AuthorRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/author-request')]
#[IsGranted('ROLE_USER')]
class AuthorRequestController extends AbstractController
{
    #[Route('', name: 'app_author_request')]
    public function index(AuthorRequestRepository $authorRequestRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_AUTHOR')) {
            $this->addFlash('info', 'Vous êtes déjà auteur !');
            return $this->redirectToRoute('app_profile');
        }

        $pendingRequest = $authorRequestRepository->findPendingForUser($user);
        $allRequests = $authorRequestRepository->findAllForUser($user);

        return $this->render('author_request/index.html.twig', [
            'pendingRequest' => $pendingRequest,
            'allRequests' => $allRequests,
        ]);
    }

    #[Route('/new', name: 'app_author_request_new', methods: ['POST'])]
    public function new(
        Request $request,
        AuthorRequestRepository $authorRequestRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_AUTHOR')) {
            $this->addFlash('info', 'Vous êtes déjà auteur !');
            return $this->redirectToRoute('app_profile');
        }

        if ($authorRequestRepository->hasUserPendingRequest($user)) {
            $this->addFlash('warning', 'Vous avez déjà une demande en attente.');
            return $this->redirectToRoute('app_author_request');
        }

        if (!$this->isCsrfTokenValid('author_request', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_author_request');
        }

        $authorRequest = new AuthorRequest();
        $authorRequest->setUser($user);
        $authorRequest->setMotivation($request->request->get('motivation'));

        $entityManager->persist($authorRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Votre demande pour devenir auteur a été envoyée ! Un administrateur l\'examinera prochainement.');

        return $this->redirectToRoute('app_author_request');
    }

    #[Route('/cancel/{id}', name: 'app_author_request_cancel', methods: ['POST'])]
    public function cancel(
        AuthorRequest $authorRequest,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($authorRequest->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$authorRequest->isPending()) {
            $this->addFlash('error', 'Cette demande ne peut plus être annulée.');
            return $this->redirectToRoute('app_author_request');
        }

        if (!$this->isCsrfTokenValid('cancel' . $authorRequest->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_author_request');
        }

        $entityManager->remove($authorRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Votre demande a été annulée.');

        return $this->redirectToRoute('app_author_request');
    }
}
