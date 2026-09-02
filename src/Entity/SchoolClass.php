<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SchoolClassRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SchoolClassRepository::class)]
#[ORM\Index(name: 'idx_school_class_grade', columns: ['grade_id'])]
#[ApiResource]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['audience:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[Groups(['audience:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'schoolClasses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Grade $grade = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $teacher = null;

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

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getTeacher(): ?string
    {
        return $this->teacher;
    }

    public function setTeacher(?string $teacher): static
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
