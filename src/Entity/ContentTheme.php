<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ContentThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContentThemeRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_content_theme_name', columns: ['name'])]
#[ORM\Index(name: 'idx_content_theme_weight', columns: ['weight'])]
#[UniqueEntity(fields: ['name'], message: 'Ce thème existe déjà.')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['theme:read']]),
        new GetCollection(
            normalizationContext: ['groups' => ['theme:read']],
            order: ['weight' => 'DESC', 'name' => 'ASC'],
            paginationEnabled: false,
        ),
    ],
)]
class ContentTheme
{
    private const string ICON_PATTERN = '/^mdi-[a-z0-9-]+$/';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['theme:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[Groups(['theme:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[Assert\Regex(pattern: self::ICON_PATTERN, message: 'Icône Material Design attendue (ex: mdi-school).')]
    #[Groups(['theme:read'])]
    private ?string $icon = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private int $weight = 0;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
