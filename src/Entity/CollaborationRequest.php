<?php

namespace App\Entity;

use App\Enum\CollaborationStatus;
use App\Repository\CollaborationRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollaborationRequestRepository::class)]
class CollaborationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collaborationRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'collaborationRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $collaborator = null;

    #[ORM\Column(type: 'string', enumType: CollaborationStatus::class)]
    private CollaborationStatus $status = CollaborationStatus::PENDING;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = CollaborationStatus::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getCollaborator(): ?User
    {
        return $this->collaborator;
    }

    public function setCollaborator(?User $collaborator): static
    {
        $this->collaborator = $collaborator;

        return $this;
    }

    public function getStatus(): CollaborationStatus
    {
        return $this->status;
    }

    public function setStatus(CollaborationStatus $status): static
    {
        $this->status = $status;

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

    public function accept(): static
    {
        $this->status = CollaborationStatus::ACCEPTED;
        $this->respondedAt = new \DateTimeImmutable();

        return $this;
    }

    public function reject(): static
    {
        $this->status = CollaborationStatus::REJECTED;
        $this->respondedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === CollaborationStatus::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === CollaborationStatus::ACCEPTED;
    }
}
