<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\DocumentVisibility;
use App\Enum\MediaMapping;
use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_document_filename', columns: ['filename'])]
#[ORM\Index(name: 'idx_document_visibility', columns: ['visibility'])]
#[ORM\Index(name: 'idx_document_theme', columns: ['theme_id'])]
#[ORM\Index(name: 'idx_document_date', columns: ['date'])]
#[UniqueEntity(fields: ['filename'], message: 'Ce fichier est déjà utilisé.', groups: ['unique'])]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['document:read', 'theme:read']],
            order: ['date' => 'DESC'],
            paginationEnabled: false,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['theme' => 'exact'])]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['document:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['document:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['document:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['document:read'])]
    private \DateTimeImmutable $date;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(length: 32, enumType: DocumentVisibility::class)]
    #[Assert\NotNull]
    private DocumentVisibility $visibility = DocumentVisibility::Visible;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    #[Groups(['document:read'])]
    private ?ContentTheme $theme = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $filename = null;

    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'filename')]
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'application/zip',
            'application/x-zip-compressed',
        ],
        mimeTypesMessage: 'Formats acceptés : PDF, Word, Excel, JPEG, PNG, ZIP.',
    )]
    private ?File $file = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->date = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getVisibility(): DocumentVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(DocumentVisibility $visibility): static
    {
        $this->visibility = $visibility;

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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;
        $this->touchOnUpload($file);

        return $this;
    }

    #[Groups(['document:read'])]
    public function getFileUrl(): ?string
    {
        return MediaMapping::Documents->url($this->filename);
    }

    #[Assert\Callback]
    public function validateFilePresence(ExecutionContextInterface $context): void
    {
        if (null === $this->filename && null === $this->file) {
            $context->buildViolation('Le fichier est obligatoire.')
                ->atPath('file')
                ->addViolation();
        }
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
