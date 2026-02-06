<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\CollaborationRequestRepository;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(CollaborationRequestRepository $collaborationRequestRepository, PostLikeRepository $postLikeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'pendingRequests' => $collaborationRequestRepository->findPendingForUser($user),
            'allRequests' => $collaborationRequestRepository->findAllForUser($user),
            'likedPosts' => $postLikeRepository->findLikedPostsByUser($user),
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/favorites', name: 'app_profile_favorites')]
    public function favorites(PostLikeRepository $postLikeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/favorites.html.twig', [
            'likedPosts' => $postLikeRepository->findLikedPostsByUser($user),
        ]);
    }
}
