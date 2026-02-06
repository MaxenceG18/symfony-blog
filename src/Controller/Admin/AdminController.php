<?php

namespace App\Controller\Admin;

use App\Entity\AuthorRequest;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\AuthorRequestStatus;
use App\Enum\CommentStatus;
use App\Form\CategoryType;
use App\Form\PostType;
use App\Repository\AuthorRequestRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(
        PostRepository $postRepository,
        UserRepository $userRepository,
        CommentRepository $commentRepository,
        CategoryRepository $categoryRepository,
        AuthorRequestRepository $authorRequestRepository
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalPosts' => $postRepository->countAll(),
            'pendingUsers' => $userRepository->countPendingUsers(),
            'pendingComments' => $commentRepository->countPending(),
            'pendingAuthorRequests' => $authorRequestRepository->countPending(),
            'totalCategories' => count($categoryRepository->findAll()),
            'latestPosts' => $postRepository->findLatest(5),
            'pendingCommentsList' => $commentRepository->findPending(),
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/users/{id}/activate', name: 'app_admin_user_activate', methods: ['POST'])]
    public function activateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($this->isCsrfTokenValid('activate'.$user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(true);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getFullName() . ' a été activé.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/deactivate', name: 'app_admin_user_deactivate', methods: ['POST'])]
    public function deactivateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->isCsrfTokenValid('deactivate'.$user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(false);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getFullName() . ' a été désactivé.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/toggle-admin', name: 'app_admin_user_toggle_admin', methods: ['POST'])]
    public function toggleAdminRole(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier vos propres rôles.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->isCsrfTokenValid('toggle-admin'.$user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $user->setRoles(['ROLE_USER']);
                $this->addFlash('success', $user->getFullName() . ' n\'est plus administrateur.');
            } else {
                $user->setRoles(['ROLE_ADMIN']);
                $this->addFlash('success', $user->getFullName() . ' est maintenant administrateur.');
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/comments', name: 'app_admin_comments')]
    public function comments(CommentRepository $commentRepository): Response
    {
        return $this->render('admin/comments/index.html.twig', [
            'comments' => $commentRepository->findAllWithRelations(),
        ]);
    }

    #[Route('/comments/{id}/approve', name: 'app_admin_comment_approve', methods: ['POST'])]
    public function approveComment(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }

        if ($this->isCsrfTokenValid('approve'.$comment->getId(), $request->request->get('_token'))) {
            $comment->setStatus(CommentStatus::APPROVED);
            $entityManager->flush();
            $this->addFlash('success', 'Le commentaire a été approuvé.');
        }

        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/comments/{id}/reject', name: 'app_admin_comment_reject', methods: ['POST'])]
    public function rejectComment(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }

        if ($this->isCsrfTokenValid('reject'.$comment->getId(), $request->request->get('_token'))) {
            $comment->setStatus(CommentStatus::REJECTED);
            $entityManager->flush();
            $this->addFlash('success', 'Le commentaire a été rejeté.');
        }

        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/comments/{id}/delete', name: 'app_admin_comment_delete', methods: ['POST'])]
    public function deleteComment(int $id, Request $request, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Le commentaire a été supprimé.');
        }

        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/categories', name: 'app_admin_categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categoryRepository->findWithPostCount(),
        ]);
    }

    #[Route('/categories/new', name: 'app_admin_category_new')]
    public function newCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été créée avec succès.');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'app_admin_category_edit')]
    public function editCategory(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a été modifiée avec succès.');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function deleteCategory(int $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $postCount = count($category->getPosts());
            $entityManager->remove($category);
            $entityManager->flush();
            
            if ($postCount > 0) {
                $this->addFlash('success', sprintf('La catégorie et ses %d article(s) ont été supprimés avec succès.', $postCount));
            } else {
                $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
            }
        }

        return $this->redirectToRoute('app_admin_categories');
    }

    #[Route('/posts', name: 'app_admin_posts')]
    public function posts(PostRepository $postRepository): Response
    {
        return $this->render('admin/posts/index.html.twig', [
            'posts' => $postRepository->findAllOrderedByDate(),
        ]);
    }

    #[Route('/author-requests', name: 'app_admin_author_requests')]
    public function authorRequests(AuthorRequestRepository $authorRequestRepository): Response
    {
        return $this->render('admin/author_requests/index.html.twig', [
            'requests' => $authorRequestRepository->findAllOrderedByDate(),
            'pendingCount' => $authorRequestRepository->countPending(),
        ]);
    }

    #[Route('/author-requests/{id}/accept', name: 'app_admin_author_request_accept', methods: ['POST'])]
    public function acceptAuthorRequest(
        int $id,
        Request $request,
        AuthorRequestRepository $authorRequestRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $authorRequest = $authorRequestRepository->find($id);

        if (!$authorRequest) {
            throw $this->createNotFoundException('Demande non trouvée');
        }

        if (!$authorRequest->isPending()) {
            $this->addFlash('warning', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_admin_author_requests');
        }

        if ($this->isCsrfTokenValid('accept' . $authorRequest->getId(), $request->request->get('_token'))) {
            $authorRequest->setStatus(AuthorRequestStatus::ACCEPTED);
            $authorRequest->setRespondedAt(new \DateTimeImmutable());
            $authorRequest->setRespondedBy($this->getUser());
            $authorRequest->setAdminComment($request->request->get('comment'));

            $user = $authorRequest->getUser();
            if (!$user->isAuthor()) {
                $user->setRoles(['ROLE_AUTHOR']);
            }

            $entityManager->flush();

            $this->addFlash('success', sprintf(
                '%s est maintenant auteur !',
                $user->getFullName()
            ));
        }

        return $this->redirectToRoute('app_admin_author_requests');
    }

    #[Route('/author-requests/{id}/reject', name: 'app_admin_author_request_reject', methods: ['POST'])]
    public function rejectAuthorRequest(
        int $id,
        Request $request,
        AuthorRequestRepository $authorRequestRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $authorRequest = $authorRequestRepository->find($id);

        if (!$authorRequest) {
            throw $this->createNotFoundException('Demande non trouvée');
        }

        if (!$authorRequest->isPending()) {
            $this->addFlash('warning', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_admin_author_requests');
        }

        if ($this->isCsrfTokenValid('reject' . $authorRequest->getId(), $request->request->get('_token'))) {
            $authorRequest->setStatus(AuthorRequestStatus::REJECTED);
            $authorRequest->setRespondedAt(new \DateTimeImmutable());
            $authorRequest->setRespondedBy($this->getUser());
            $authorRequest->setAdminComment($request->request->get('comment'));

            $entityManager->flush();

            $this->addFlash('success', 'La demande a été refusée.');
        }

        return $this->redirectToRoute('app_admin_author_requests');
    }

    #[Route('/users/{id}/toggle-author', name: 'app_admin_user_toggle_author', methods: ['POST'])]
    public function toggleAuthorRole(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($this->isCsrfTokenValid('toggle-author' . $user->getId(), $request->request->get('_token'))) {
            if ($user->isAuthor()) {
                $user->setRoles(['ROLE_USER']);
                $this->addFlash('success', sprintf('%s n\'est plus auteur.', $user->getFullName()));
            } else {
                $user->setRoles(['ROLE_AUTHOR']);
                $this->addFlash('success', sprintf('%s est maintenant auteur !', $user->getFullName()));
            }
            
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
