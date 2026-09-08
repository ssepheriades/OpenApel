<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\MediaMapping;
use App\Enum\PostState;
use App\Repository\PostRepository;
use App\Service\ContentSlugger;
use App\Validator\Constraints\ExclusiveGradeOrClass;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_post_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_post_theme', columns: ['theme_id'])]
#[ORM\Index(name: 'idx_post_state', columns: ['state'])]
#[UniqueEntity(fields: ['slug'], message: 'Cette adresse est déjà utilisée.')]
#[ExclusiveGradeOrClass]
#[Vich\Uploadable]
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
    #[ApiProperty(identifier: false)]
    #[Groups(['post:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['post:read'])]
    private ?string $title = null;

    // Blank is allowed in the form: ContentSlugListener fills it on persist.
    #[ORM\Column(length: 80)]
    #[ApiProperty(identifier: true)]
    #[Assert\Length(min: ContentSlugger::MIN_LENGTH, max: ContentSlugger::MAX_LENGTH)]
    #[Assert\Regex(pattern: ContentSlugger::PATTERN, message: 'Utilisez uniquement des lettres minuscules, des chiffres et des tirets.')]
    #[Groups(['post:read'])]
    private ?string $slug = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageFilename = null;

    #[Vich\UploadableField(mapping: 'photos', fileNameProperty: 'coverImageFilename')]
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Formats acceptés : JPEG, PNG, WebP.')]
    private ?File $coverImageFile = null;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $normalized = null === $slug ? null : trim($slug);
        $this->slug = '' === $normalized ? null : $normalized;

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

    public function getCoverImageFilename(): ?string
    {
        return $this->coverImageFilename;
    }

    public function setCoverImageFilename(?string $coverImageFilename): static
    {
        $this->coverImageFilename = $coverImageFilename;

        return $this;
    }

    public function getCoverImageFile(): ?File
    {
        return $this->coverImageFile;
    }

    public function setCoverImageFile(?File $coverImageFile): static
    {
        $this->coverImageFile = $coverImageFile;
        $this->touchOnUpload($coverImageFile);

        return $this;
    }

    #[Groups(['post:read'])]
    public function getCoverImageUrl(): ?string
    {
        return MediaMapping::Photos->url($this->coverImageFilename);
    }

    /**
     * Vich only moves the file when Doctrine detects a change on the entity,
     * so a fresh upload must dirty a mapped column.
     */
    private function touchOnUpload(?File $file): void
    {
        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
}
