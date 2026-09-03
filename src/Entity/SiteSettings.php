<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SiteSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Singleton entity: exactly one row (id = 1, enforced by a CHECK constraint in the migration).
 * Holds the per-instance branding edited once by the staff from the back-office.
 */
#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
#[ORM\Table(name: 'site_settings')]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class SiteSettings
{
    public const string DEFAULT_SITE_NAME = 'OpenApel';
    public const string DEFAULT_PRIMARY_COLOR = '#272857';
    public const string DEFAULT_SECONDARY_COLOR = '#2ed8ff';
    public const string DEFAULT_SCHOOL_YEAR_START = '2000-08-01';
    public const string DEFAULT_SCHOOL_YEAR_END = '2000-07-31';
    private const string HEX_COLOR_PATTERN = '/^#[0-9a-fA-F]{6}$/';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private string $siteName = self::DEFAULT_SITE_NAME;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $baseline = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoFilename = null;

    #[Vich\UploadableField(mapping: 'branding', fileNameProperty: 'logoFilename')]
    #[Assert\File(maxSize: '2M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'], mimeTypesMessage: 'Formats acceptés : JPEG, PNG, WebP, SVG.')]
    private ?File $logoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $faviconFilename = null;

    // No SVG for the favicon: it is referenced from <link rel="icon">, keep it to raster/ico formats.
    #[Vich\UploadableField(mapping: 'branding', fileNameProperty: 'faviconFilename')]
    #[Assert\File(maxSize: '512k', mimeTypes: ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'], mimeTypesMessage: 'Formats acceptés : PNG, ICO.')]
    private ?File $faviconFile = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $contactEmail = null;

    #[ORM\Column]
    private bool $contactEmailEnabled = true;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 255)]
    private ?string $facebookUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 255)]
    private ?string $instagramUrl = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: self::HEX_COLOR_PATTERN, message: 'Couleur hexadécimale attendue (ex: #272857).')]
    private string $primaryColor = self::DEFAULT_PRIMARY_COLOR;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: self::HEX_COLOR_PATTERN, message: 'Couleur hexadécimale attendue (ex: #2ed8ff).')]
    private string $secondaryColor = self::DEFAULT_SECONDARY_COLOR;

    /**
     * Recurring school-year start (month and day only; the stored year is ignored at runtime).
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private \DateTimeImmutable $schoolYearStart;

    /**
     * Recurring school-year end (month and day only; the stored year is ignored at runtime).
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private \DateTimeImmutable $schoolYearEnd;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->schoolYearStart = new \DateTimeImmutable(self::DEFAULT_SCHOOL_YEAR_START);
        $this->schoolYearEnd = new \DateTimeImmutable(self::DEFAULT_SCHOOL_YEAR_END);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiteName(): string
    {
        return $this->siteName;
    }

    public function setSiteName(string $siteName): self
    {
        $this->siteName = trim($siteName);

        return $this;
    }

    public function getBaseline(): ?string
    {
        return $this->baseline;
    }

    public function setBaseline(?string $baseline): self
    {
        $this->baseline = self::normalizeOptionalString($baseline);

        return $this;
    }

    public function getLogoFilename(): ?string
    {
        return $this->logoFilename;
    }

    public function setLogoFilename(?string $logoFilename): self
    {
        $this->logoFilename = $logoFilename;

        return $this;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogoFile(?File $logoFile = null): self
    {
        $this->logoFile = $logoFile;
        $this->touchOnUpload($logoFile);

        return $this;
    }

    public function getFaviconFilename(): ?string
    {
        return $this->faviconFilename;
    }

    public function setFaviconFilename(?string $faviconFilename): self
    {
        $this->faviconFilename = $faviconFilename;

        return $this;
    }

    public function getFaviconFile(): ?File
    {
        return $this->faviconFile;
    }

    public function setFaviconFile(?File $faviconFile = null): self
    {
        $this->faviconFile = $faviconFile;
        $this->touchOnUpload($faviconFile);

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): self
    {
        $this->contactEmail = self::normalizeOptionalString($contactEmail);

        return $this;
    }

    public function isContactEmailEnabled(): bool
    {
        return $this->contactEmailEnabled;
    }

    public function setContactEmailEnabled(bool $contactEmailEnabled): self
    {
        $this->contactEmailEnabled = $contactEmailEnabled;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): self
    {
        $this->facebookUrl = self::normalizeOptionalString($facebookUrl);

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(?string $instagramUrl): self
    {
        $this->instagramUrl = self::normalizeOptionalString($instagramUrl);

        return $this;
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): self
    {
        $this->primaryColor = strtolower(trim($primaryColor));

        return $this;
    }

    public function getSecondaryColor(): string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(string $secondaryColor): self
    {
        $this->secondaryColor = strtolower(trim($secondaryColor));

        return $this;
    }

    public function getSchoolYearStart(): \DateTimeImmutable
    {
        return $this->schoolYearStart;
    }

    public function setSchoolYearStart(\DateTimeImmutable $schoolYearStart): self
    {
        $this->schoolYearStart = $schoolYearStart;

        return $this;
    }

    public function getSchoolYearEnd(): \DateTimeImmutable
    {
        return $this->schoolYearEnd;
    }

    public function setSchoolYearEnd(\DateTimeImmutable $schoolYearEnd): self
    {
        $this->schoolYearEnd = $schoolYearEnd;

        return $this;
    }

    #[Assert\Callback]
    public function validateDistinctSchoolYearBounds(ExecutionContextInterface $context): void
    {
        if ($this->schoolYearStart->format('m-d') === $this->schoolYearEnd->format('m-d')) {
            $context->buildViolation('Le début et la fin de l\'année scolaire ne peuvent pas être le même jour.')
                ->atPath('schoolYearEnd')
                ->addViolation();
        }
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->siteName;
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
