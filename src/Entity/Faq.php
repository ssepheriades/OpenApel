<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\ContentTheme;
use App\Enum\FaqVisibility;
use App\Repository\FaqRepository;
use App\Validator\Constraints\ExclusiveGradeOrClass;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
#[ORM\Index(name: 'idx_faq_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_faq_visibility', columns: ['visibility'])]
#[ORM\Index(name: 'idx_faq_theme', columns: ['theme'])]
#[ORM\HasLifecycleCallbacks]
#[ExclusiveGradeOrClass]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['faq:read', 'audience:read']]),
        new GetCollection(
            normalizationContext: ['groups' => ['faq:read', 'audience:read']],
            order: ['createdAt' => 'DESC'],
            paginationEnabled: false,
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['grades' => 'exact', 'schoolClasses' => 'exact'])]
class Faq implements AudienceTargetedInterface
{
    use AudienceTargetsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['faq:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['faq:read'])]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['faq:read'])]
    private ?string $answer = null;

    #[ORM\Column(length: 32, enumType: FaqVisibility::class)]
    #[Assert\NotNull]
    private FaqVisibility $visibility = FaqVisibility::Visible;

    #[ORM\Column(length: 32, enumType: ContentTheme::class)]
    #[Assert\NotNull]
    #[Groups(['faq:read'])]
    private ContentTheme $theme = ContentTheme::Autre;

    #[ORM\Column]
    #[Groups(['faq:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->initializeAudienceTargets();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    public function getVisibility(): FaqVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(FaqVisibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getTheme(): ContentTheme
    {
        return $this->theme;
    }

    public function setTheme(ContentTheme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedTimestamp(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}
