<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use App\Enum\CommentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Find approved comments for a post
     */
    public function findApprovedByPost(Post $post): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.author', 'a')
            ->addSelect('a')
            ->andWhere('c.post = :post')
            ->andWhere('c.status = :status')
            ->setParameter('post', $post)
            ->setParameter('status', CommentStatus::APPROVED)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all pending comments
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.author', 'a')
            ->leftJoin('c.post', 'p')
            ->addSelect('a', 'p')
            ->andWhere('c.status = :status')
            ->setParameter('status', CommentStatus::PENDING)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count pending comments
     */
    public function countPending(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.status = :status')
            ->setParameter('status', CommentStatus::PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all comments with relations for admin
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.author', 'a')
            ->leftJoin('c.post', 'p')
            ->addSelect('a', 'p')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
