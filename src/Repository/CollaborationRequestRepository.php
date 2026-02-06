<?php

namespace App\Repository;

use App\Entity\CollaborationRequest;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\CollaborationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CollaborationRequest>
 */
class CollaborationRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollaborationRequest::class);
    }

    /**
     * Find pending requests for a user
     */
    public function findPendingForUser(User $user): array
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.post', 'p')
            ->leftJoin('p.author', 'a')
            ->addSelect('p', 'a')
            ->andWhere('cr.collaborator = :user')
            ->andWhere('cr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', CollaborationStatus::PENDING)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all requests for a user
     */
    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.post', 'p')
            ->leftJoin('p.author', 'a')
            ->addSelect('p', 'a')
            ->andWhere('cr.collaborator = :user')
            ->setParameter('user', $user)
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find accepted collaborators for a post
     */
    public function findAcceptedForPost(Post $post): array
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.collaborator', 'c')
            ->addSelect('c')
            ->andWhere('cr.post = :post')
            ->andWhere('cr.status = :status')
            ->setParameter('post', $post)
            ->setParameter('status', CollaborationStatus::ACCEPTED)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count pending requests for a user
     */
    public function countPendingForUser(User $user): int
    {
        return $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->andWhere('cr.collaborator = :user')
            ->andWhere('cr.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', CollaborationStatus::PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Check if a collaboration request already exists
     */
    public function findExisting(Post $post, User $collaborator): ?CollaborationRequest
    {
        return $this->createQueryBuilder('cr')
            ->andWhere('cr.post = :post')
            ->andWhere('cr.collaborator = :collaborator')
            ->setParameter('post', $post)
            ->setParameter('collaborator', $collaborator)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
