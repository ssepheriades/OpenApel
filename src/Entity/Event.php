<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\EventState;
use App\Enum\EventType;
use App\Enum\EventVisibility;
use App\Repository\EventRepository;
use App\Validator\Constraints\ExclusiveGradeOrClass;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ExclusiveGradeOrClass]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['event:read', 'audience:read']]),
        new GetCollection(
            normalizationContext: ['groups' => ['event:read', 'audience:read']],
            order: ['startsAt' => 'ASC'],
            paginationEnabled: false,
        ),
    ],
)]
#[ApiFilter(DateFilter::class, properties: ['startsAt'])]
#[ApiFilter(SearchFilter::class, properties: ['grades' => 'exact', 'schoolClasses' => 'exact'])]
#[Vich\Uploadable]
class Event implements AudienceTargetedInterface
{
    use AudienceTargetsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 128, nullable: true)]
    #[Groups(['event:read'])]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['event:read'])]
    private ?string $ticketingUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['event:read'])]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 32, enumType: EventType::class)]
    #[Groups(['event:read'])]
    private ?EventType $type = null;

    #[ORM\Column(length: 32, enumType: EventState::class)]
    #[Groups(['event:read'])]
    private ?EventState $state = null;

    #[ORM\Column(length: 32, enumType: EventVisibility::class, nullable: true)]
    #[Groups(['event:read'])]
    private ?EventVisibility $visibility = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['event:read'])]
    private ?bool $isAllDay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroImageFilename = null;

    #[Vich\UploadableField(mapping: 'photos', fileNameProperty: 'heroImageFilename')]
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Formats acceptés : JPEG, PNG, WebP.')]
    private ?File $heroImageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $flyerImageFilename = null;

    #[Vich\UploadableField(mapping: 'photos', fileNameProperty: 'flyerImageFilename')]
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Formats acceptés : JPEG, PNG, WebP.')]
    private ?File $flyerImageFile = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getTicketingUrl(): ?string
    {
        return $this->ticketingUrl;
    }

    public function setTicketingUrl(?string $ticketingUrl): static
    {
        $this->ticketingUrl = $ticketingUrl;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(?EventType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): ?EventState
    {
        return $this->state;
    }

    public function setState(?EventState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getVisibility(): ?EventVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(?EventVisibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function isAllDay(): ?bool
    {
        return $this->isAllDay;
    }

    public function setIsAllDay(?bool $isAllDay): static
    {
        $this->isAllDay = $isAllDay;

        return $this;
    }

    public function getHeroImageFilename(): ?string
    {
        return $this->heroImageFilename;
    }

    public function setHeroImageFilename(?string $heroImageFilename): static
    {
        $this->heroImageFilename = $heroImageFilename;

        return $this;
    }

    public function getHeroImageFile(): ?File
    {
        return $this->heroImageFile;
    }

    public function setHeroImageFile(?File $heroImageFile): static
    {
        $this->heroImageFile = $heroImageFile;

        return $this;
    }

    public function getFlyerImageFilename(): ?string
    {
        return $this->flyerImageFilename;
    }

    public function setFlyerImageFilename(?string $flyerImageFilename): static
    {
        $this->flyerImageFilename = $flyerImageFilename;

        return $this;
    }

    public function getFlyerImageFile(): ?File
    {
        return $this->flyerImageFile;
    }

    public function setFlyerImageFile(?File $flyerImageFile): static
    {
        $this->flyerImageFile = $flyerImageFile;

        return $this;
    }

    #[Groups(['event:read'])]
    public function getHeroImageUrl(): ?string
    {
        return null !== $this->heroImageFilename ? '/uploads/photos/' . $this->heroImageFilename : null;
    }

    #[Groups(['event:read'])]
    public function getFlyerImageUrl(): ?string
    {
        return null !== $this->flyerImageFilename ? '/uploads/photos/' . $this->flyerImageFilename : null;
    }
}
