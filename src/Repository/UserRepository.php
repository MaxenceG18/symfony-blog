<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Find all active users
     */
    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users excluding one (for collaboration selection) - Only authors and admins
     */
    public function findAllExcept(User $user): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id != :userId')
            ->andWhere('u.isActive = :active')
            ->andWhere('u.roles LIKE :roleAuthor OR u.roles LIKE :roleAdmin')
            ->setParameter('userId', $user->getId())
            ->setParameter('active', true)
            ->setParameter('roleAuthor', '%ROLE_AUTHOR%')
            ->setParameter('roleAdmin', '%ROLE_ADMIN%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all authors (users with ROLE_AUTHOR or ROLE_ADMIN)
     */
    public function findAuthors(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->andWhere('u.roles LIKE :roleAuthor OR u.roles LIKE :roleAdmin')
            ->setParameter('active', true)
            ->setParameter('roleAuthor', '%ROLE_AUTHOR%')
            ->setParameter('roleAdmin', '%ROLE_ADMIN%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all users with admin role
     */
    public function findAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count pending users (not activated)
     */
    public function countPendingUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', false)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
