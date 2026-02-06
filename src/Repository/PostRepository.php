<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Find all posts ordered by publication date
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->addSelect('a', 'c')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find latest posts with limit
     */
    public function findLatest(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->addSelect('a', 'c')
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find posts by category
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->addSelect('a')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find post by slug with all relations
     */
    public function findOneBySlugWithRelations(string $slug): ?Post
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.comments', 'com')
            ->leftJoin('com.author', 'ca')
            ->leftJoin('p.collaborationRequests', 'cr')
            ->leftJoin('cr.collaborator', 'col')
            ->addSelect('a', 'c', 'com', 'ca', 'cr', 'col')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Search posts by title or content
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->addSelect('a', 'c')
            ->andWhere('p.title LIKE :query OR p.content LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total posts
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllQuery()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->addSelect('a', 'c')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();
    }

    public function findByCategoryQuery(Category $category)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->addSelect('a')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();
    }

    public function searchQuery(string $query)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->leftJoin('p.category', 'c')
            ->addSelect('a', 'c')
            ->andWhere('p.title LIKE :query OR p.content LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery();
    }
}
