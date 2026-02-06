<?php

namespace App\Controller;

use App\Entity\CollaborationRequest;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\CollaborationRequestRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('', name: 'app_post_index')]
    public function index(
        PostRepository $postRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $categorySlug = $request->query->get('category');
        $searchQuery = $request->query->get('q');
        
        if ($searchQuery) {
            $query = $postRepository->searchQuery($searchQuery);
        } elseif ($categorySlug) {
            $category = $categoryRepository->findOneBy(['slug' => $categorySlug]);
            $query = $category ? $postRepository->findByCategoryQuery($category) : [];
        } else {
            $query = $postRepository->findAllQuery();
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
            'categories' => $categoryRepository->findAllOrdered(),
            'currentCategory' => $categorySlug,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/new', name: 'app_post_new')]
    #[IsGranted('ROLE_AUTHOR')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            $post->setAuthor($currentUser);
            $entityManager->persist($post);

            // Handle collaborators from dynamic form
            $collaboratorIds = $request->request->all('collaborators') ?? [];
            $collaboratorCount = 0;
            
            foreach ($collaboratorIds as $collaboratorId) {
                if (!empty($collaboratorId)) {
                    $collaborator = $userRepository->find($collaboratorId);
                    if ($collaborator && $collaborator !== $currentUser) {
                        $collaborationRequest = new CollaborationRequest();
                        $collaborationRequest->setPost($post);
                        $collaborationRequest->setCollaborator($collaborator);
                        $entityManager->persist($collaborationRequest);
                        $collaboratorCount++;
                    }
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'article a été créé avec succès !');
            
            if ($collaboratorCount > 0) {
                $this->addFlash('info', sprintf('%d demande(s) de collaboration envoyée(s) aux co-auteurs.', $collaboratorCount));
            }

            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }

        // Préparer la liste des utilisateurs pour le JS
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $availableCollaborators = $userRepository->findAllExcept($currentUser);
        $collaboratorsData = array_map(fn($user) => [
            'id' => $user->getId(),
            'name' => $user->getFullName()
        ], $availableCollaborators);

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'available_collaborators' => $collaboratorsData,
        ]);
    }

    #[Route('/{slug}', name: 'app_post_show')]
    public function show(
        string $slug, 
        PostRepository $postRepository, 
        CollaborationRequestRepository $collaborationRequestRepository,
        EntityManagerInterface $entityManager,
        PostLikeRepository $postLikeRepository
    ): Response
    {
        $post = $postRepository->findOneBySlugWithRelations($slug);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Incrémenter le compteur de vues
        $post->incrementViewCount();
        $entityManager->flush();

        $commentForm = null;
        $isLikedByUser = false;
        
        if ($this->getUser()) {
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment, [
                'action' => $this->generateUrl('app_post_comment', ['slug' => $post->getSlug()])
            ]);
            
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $isLikedByUser = $postLikeRepository->isLikedByUser($post, $user);
        }

        $confirmedCollaborators = $collaborationRequestRepository->findAcceptedForPost($post);

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm?->createView(),
            'confirmedCollaborators' => $confirmedCollaborators,
            'isLikedByUser' => $isLikedByUser,
        ]);
    }

    #[Route('/{slug}/like', name: 'app_post_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(
        string $slug,
        PostRepository $postRepository,
        PostLikeRepository $postLikeRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $post = $postRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $existingLike = $postLikeRepository->findByPostAndUser($post, $user);

        if ($existingLike) {
            // Unlike
            $entityManager->remove($existingLike);
            $entityManager->flush();
            $this->addFlash('success', 'Article retiré de vos favoris.');
        } else {
            // Like
            $like = new PostLike();
            $like->setPost($post);
            $like->setUser($user);
            $entityManager->persist($like);
            $entityManager->flush();
            $this->addFlash('success', 'Article ajouté à vos favoris !');
        }

        // Return to previous page or post
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_post_show', ['slug' => $slug]);
    }

    #[Route('/{slug}/edit', name: 'app_post_edit')]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit(
        string $slug,
        Request $request,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager,
        CollaborationRequestRepository $collaborationRequestRepository
    ): Response {
        $post = $postRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        if ($post->getAuthor() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres articles.');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle new collaborators
            $collaborators = $form->get('collaborators')->getData();
            foreach ($collaborators as $collaborator) {
                // Check if request already exists
                $existingRequest = $collaborationRequestRepository->findExisting($post, $collaborator);
                if (!$existingRequest) {
                    $collaborationRequest = new CollaborationRequest();
                    $collaborationRequest->setPost($post);
                    $collaborationRequest->setCollaborator($collaborator);
                    $entityManager->persist($collaborationRequest);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'article a été modifié avec succès !');

            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/delete', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function delete(string $slug, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        if ($post->getAuthor() !== $currentUser && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres articles.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'L\'article a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_post_index');
    }

    #[Route('/{slug}/comment', name: 'app_post_comment', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addComment(string $slug, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Check if user is active
        if (!$user->isActive()) {
            $this->addFlash('error', 'Votre compte doit être activé par un administrateur pour pouvoir commenter.');
            return $this->redirectToRoute('app_post_show', ['slug' => $slug]);
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($user);
            $comment->setPost($post);
            
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été soumis et sera visible après validation par un administrateur.');
        }

        return $this->redirectToRoute('app_post_show', ['slug' => $slug]);
    }
}
