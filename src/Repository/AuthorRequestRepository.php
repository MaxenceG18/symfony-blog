<?php

namespace App\Repository;

use App\Entity\AuthorRequest;
use App\Entity\User;
use App\Enum\AuthorRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthorRequest>
 */
class AuthorRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthorRequest::class);
    }

    public function findPendingForUser(User $user): ?AuthorRequest
    {
        return $this->findOneBy([
            'user' => $user,
            'status' => AuthorRequestStatus::PENDING,
        ]);
    }

    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('ar')
            ->where('ar.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllPending(): array
    {
        return $this->createQueryBuilder('ar')
            ->join('ar.user', 'u')
            ->addSelect('u')
            ->where('ar.status = :status')
            ->setParameter('status', AuthorRequestStatus::PENDING)
            ->orderBy('ar.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('ar')
            ->join('ar.user', 'u')
            ->addSelect('u')
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPending(): int
    {
        return $this->count(['status' => AuthorRequestStatus::PENDING]);
    }

    public function hasUserPendingRequest(User $user): bool
    {
        return $this->findPendingForUser($user) !== null;
    }
}
