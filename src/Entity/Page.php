<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\MediaMapping;
use App\Enum\PageKind;
use App\Enum\PageSlug;
use App\Repository\PageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * One row per locked catalogue slug. Content is edited in EasyAdmin; slugs are not created by staff.
 */
#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(name: 'page')]
#[ORM\UniqueConstraint(name: 'uniq_page_slug', columns: ['slug'])]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, enumType: PageSlug::class)]
    #[Assert\NotNull]
    private PageSlug $slug;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private string $title = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $subtitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 50000)]
    private ?string $body = null;

    #[ORM\Column]
    private bool $visible = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageFilename = null;

    #[Vich\UploadableField(mapping: 'photos', fileNameProperty: 'coverImageFilename')]
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Formats acceptés : JPEG, PNG, WebP.')]
    private ?File $coverImageFile = null;

    public static function fromSlug(PageSlug $slug): self
    {
        $page = new self();
        $page->slug = $slug;
        $page->title = $slug->defaultTitle();
        $page->subtitle = $slug->defaultSubtitle();
        $page->body = $slug->defaultBody();

        return $page;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): PageSlug
    {
        return $this->slug;
    }

    public function getSlugValue(): string
    {
        return $this->slug->value;
    }

    public function getKind(): PageKind
    {
        return $this->slug->kind();
    }

    public function getKindLabel(): string
    {
        return $this->getKind()->label();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = self::normalizeOptionalString($subtitle);

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = self::normalizeOptionalString($body);

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getVisibilityLabel(): string
    {
        if (!$this->slug->usesVisibility()) {
            return '—';
        }

        return $this->visible ? 'Oui' : 'Non';
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCoverImageFilename(): ?string
    {
        return $this->coverImageFilename;
    }

    public function setCoverImageFilename(?string $coverImageFilename): self
    {
        $this->coverImageFilename = $coverImageFilename;

        return $this;
    }

    public function getCoverImageFile(): ?File
    {
        return $this->coverImageFile;
    }

    public function setCoverImageFile(?File $coverImageFile): self
    {
        $this->coverImageFile = $coverImageFile;
        $this->touchOnUpload($coverImageFile);

        return $this;
    }

    public function getCoverImageUrl(): ?string
    {
        return MediaMapping::Photos->url($this->coverImageFilename);
    }

    #[Assert\Callback]
    public function validateRequiredBody(ExecutionContextInterface $context): void
    {
        if (!$this->slug->usesBody() || PageKind::Document !== $this->slug->kind()) {
            return;
        }

        if (null === $this->body || '' === $this->body) {
            $context->buildViolation('Le contenu de cette page est obligatoire.')
                ->atPath('body')
                ->addViolation();
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title;
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

    private static function normalizeOptionalString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
