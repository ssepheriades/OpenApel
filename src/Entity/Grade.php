<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\GradeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
#[ORM\Index(name: 'idx_grade_weight', columns: ['weight'])]
#[ApiResource]
class Grade
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

    #[ORM\Column]
    #[Assert\NotNull]
    private int $weight = 0;

    /**
     * @var Collection<int, SchoolClass>
     */
    #[ORM\OneToMany(targetEntity: SchoolClass::class, mappedBy: 'grade')]
    private Collection $schoolClasses;

    public function __construct()
    {
        $this->schoolClasses = new ArrayCollection();
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

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    public function getSchoolClasses(): Collection
    {
        return $this->schoolClasses;
    }

    public function addSchoolClass(SchoolClass $schoolClass): static
    {
        if (!$this->schoolClasses->contains($schoolClass)) {
            $this->schoolClasses->add($schoolClass);
            $schoolClass->setGrade($this);
        }

        return $this;
    }

    public function removeSchoolClass(SchoolClass $schoolClass): static
    {
        $this->schoolClasses->removeElement($schoolClass);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
