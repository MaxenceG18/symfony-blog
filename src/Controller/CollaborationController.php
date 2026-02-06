<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CollaborationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/collaboration')]
#[IsGranted('ROLE_USER')]
class CollaborationController extends AbstractController
{
    #[Route('', name: 'app_collaboration_index')]
    public function index(CollaborationRequestRepository $collaborationRequestRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('collaboration/index.html.twig', [
            'pendingRequests' => $collaborationRequestRepository->findPendingForUser($user),
            'allRequests' => $collaborationRequestRepository->findAllForUser($user),
        ]);
    }

    #[Route('/{id}/accept', name: 'app_collaboration_accept', methods: ['POST'])]
    public function accept(
        int $id,
        Request $request,
        CollaborationRequestRepository $collaborationRequestRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        $collaborationRequest = $collaborationRequestRepository->find($id);

        if (!$collaborationRequest) {
            throw $this->createNotFoundException('Demande de collaboration non trouvée');
        }

        // Verify the request belongs to the current user
        if ($collaborationRequest->getCollaborator() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accepter cette demande');
        }

        if ($this->isCsrfTokenValid('accept'.$collaborationRequest->getId(), $request->request->get('_token'))) {
            $collaborationRequest->accept();
            $entityManager->flush();

            $this->addFlash('success', 'Vous avez accepté la collaboration sur l\'article "' . $collaborationRequest->getPost()->getTitle() . '"');
        }

        return $this->redirectToRoute('app_collaboration_index');
    }

    #[Route('/{id}/reject', name: 'app_collaboration_reject', methods: ['POST'])]
    public function reject(
        int $id,
        Request $request,
        CollaborationRequestRepository $collaborationRequestRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        $collaborationRequest = $collaborationRequestRepository->find($id);

        if (!$collaborationRequest) {
            throw $this->createNotFoundException('Demande de collaboration non trouvée');
        }

        // Verify the request belongs to the current user
        if ($collaborationRequest->getCollaborator() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à refuser cette demande');
        }

        if ($this->isCsrfTokenValid('reject'.$collaborationRequest->getId(), $request->request->get('_token'))) {
            $collaborationRequest->reject();
            $entityManager->flush();

            $this->addFlash('info', 'Vous avez refusé la collaboration sur l\'article "' . $collaborationRequest->getPost()->getTitle() . '"');
        }

        return $this->redirectToRoute('app_collaboration_index');
    }
}
