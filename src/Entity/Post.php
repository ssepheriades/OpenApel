<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\PostState;
use App\Repository\PostRepository;
use App\Validator\Constraints\ExclusiveGradeOrClass;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(name: 'idx_post_theme', columns: ['theme_id'])]
#[ORM\Index(name: 'idx_post_state', columns: ['state'])]
#[ExclusiveGradeOrClass]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['post:read', 'audience:read', 'theme:read']]),
        new GetCollection(
            normalizationContext: ['groups' => ['post:read', 'audience:read', 'theme:read']],
            order: ['createdAt' => 'DESC'],
            paginationEnabled: false,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['grades' => 'exact', 'schoolClasses' => 'exact'])]
class Post implements AudienceTargetedInterface
{
    use AudienceTargetsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['post:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['post:read'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['post:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private ?int $viewCount = null;

    #[ORM\Column(length: 32, enumType: PostState::class)]
    #[Assert\NotNull]
    private PostState $state = PostState::Draft;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    #[Groups(['post:read'])]
    private ?ContentTheme $theme = null;

    public function __construct()
    {
        $this->initializeAudienceTargets();
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
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

    public function getViewCount(): ?int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): static
    {
        $this->viewCount = $viewCount;

        return $this;
    }

    public function getState(): PostState
    {
        return $this->state;
    }

    public function setState(PostState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getTheme(): ?ContentTheme
    {
        return $this->theme;
    }

    public function setTheme(ContentTheme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }
}
