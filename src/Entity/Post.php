<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Un article avec ce titre existe déjà')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'Le titre doit contenir au moins {{ limit }} caractères')]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire')]
    #[Assert\Length(min: 50, minMessage: 'Le contenu doit contenir au moins {{ limit }} caractères')]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'La catégorie est obligatoire')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    /**
     * @var Collection<int, CollaborationRequest>
     */
    #[ORM\OneToMany(targetEntity: CollaborationRequest::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $collaborationRequests;

    /**
     * @var Collection<int, PostLike>
     */
    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $likes;

    #[ORM\Column(options: ['default' => 0])]
    private int $viewCount = 0;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->collaborationRequests = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->publishedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function generateSlug(): void
    {
        if ($this->slug === null && $this->title !== null) {
            $this->slug = $this->slugify($this->title);
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        $this->slug = $this->slugify($title);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function getApprovedComments(): Collection
    {
        return $this->comments->filter(
            fn(Comment $comment) => $comment->getStatus() === \App\Enum\CommentStatus::APPROVED
        );
    }

    /**
     * @return Collection<int, CollaborationRequest>
     */
    public function getCollaborationRequests(): Collection
    {
        return $this->collaborationRequests;
    }

    public function addCollaborationRequest(CollaborationRequest $collaborationRequest): static
    {
        if (!$this->collaborationRequests->contains($collaborationRequest)) {
            $this->collaborationRequests->add($collaborationRequest);
            $collaborationRequest->setPost($this);
        }

        return $this;
    }

    public function removeCollaborationRequest(CollaborationRequest $collaborationRequest): static
    {
        if ($this->collaborationRequests->removeElement($collaborationRequest)) {
            // set the owning side to null (unless already changed)
            if ($collaborationRequest->getPost() === $this) {
                $collaborationRequest->setPost(null);
            }
        }

        return $this;
    }

    /**
     * Get all confirmed collaborators (accepted collaboration requests)
     */
    public function getConfirmedCollaborators(): Collection
    {
        return $this->collaborationRequests
            ->filter(fn(CollaborationRequest $request) => $request->getStatus() === \App\Enum\CollaborationStatus::ACCEPTED)
            ->map(fn(CollaborationRequest $request) => $request->getCollaborator());
    }

    /**
     * Get all authors (main author + confirmed collaborators)
     */
    public function getAllAuthors(): array
    {
        $authors = [$this->author];
        foreach ($this->getConfirmedCollaborators() as $collaborator) {
            $authors[] = $collaborator;
        }
        return $authors;
    }

    public function getExcerpt(int $length = 150): string
    {
        $content = strip_tags($this->content);
        if (strlen($content) <= $length) {
            return $content;
        }
        return substr($content, 0, $length) . '...';
    }

    private function slugify(string $text): string
    {
        // Transliterate non-ASCII characters
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        // Replace non-alphanumeric characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        // Remove leading/trailing hyphens
        $text = trim($text, '-');
        // Add unique suffix to avoid duplicates
        return $text . '-' . uniqid();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setPost($this);
        }

        return $this;
    }

    public function removeLike(PostLike $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }

        return $this;
    }

    public function getLikeCount(): int
    {
        return $this->likes->count();
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): static
    {
        $this->viewCount = $viewCount;

        return $this;
    }

    public function incrementViewCount(): static
    {
        $this->viewCount++;

        return $this;
    }
}
