<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostLike>
 */
class PostLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostLike::class);
    }

    public function findByPostAndUser(Post $post, User $user): ?PostLike
    {
        return $this->findOneBy(['post' => $post, 'user' => $user]);
    }

    public function countByPost(Post $post): int
    {
        return $this->count(['post' => $post]);
    }

    public function isLikedByUser(Post $post, User $user): bool
    {
        return $this->findByPostAndUser($post, $user) !== null;
    }

    /**
     * @return PostLike[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('pl')
            ->join('pl.post', 'p')
            ->addSelect('p')
            ->where('pl.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Post[]
     */
    public function findLikedPostsByUser(User $user): array
    {
        $likes = $this->createQueryBuilder('pl')
            ->join('pl.post', 'p')
            ->addSelect('p')
            ->where('pl.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn(PostLike $like) => $like->getPost(), $likes);
    }
}
