<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('', name: 'app_category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findWithPostCount(),
        ]);
    }

    #[Route('/{slug}', name: 'app_category_show')]
    public function show(string $slug, CategoryRepository $categoryRepository, PostRepository $postRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'posts' => $postRepository->findByCategory($category),
        ]);
    }
}
