<?php

namespace App\Entity;

use App\Enum\AuthorRequestStatus;
use App\Repository\AuthorRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorRequestRepository::class)]
class AuthorRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', enumType: AuthorRequestStatus::class)]
    private AuthorRequestStatus $status = AuthorRequestStatus::PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motivation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\ManyToOne]
    private ?User $respondedBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adminComment = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = AuthorRequestStatus::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): AuthorRequestStatus
    {
        return $this->status;
    }

    public function setStatus(AuthorRequestStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    public function setMotivation(?string $motivation): static
    {
        $this->motivation = $motivation;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?\DateTimeImmutable $respondedAt): static
    {
        $this->respondedAt = $respondedAt;

        return $this;
    }

    public function getRespondedBy(): ?User
    {
        return $this->respondedBy;
    }

    public function setRespondedBy(?User $respondedBy): static
    {
        $this->respondedBy = $respondedBy;

        return $this;
    }

    public function getAdminComment(): ?string
    {
        return $this->adminComment;
    }

    public function setAdminComment(?string $adminComment): static
    {
        $this->adminComment = $adminComment;

        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === AuthorRequestStatus::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === AuthorRequestStatus::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === AuthorRequestStatus::REJECTED;
    }
}
