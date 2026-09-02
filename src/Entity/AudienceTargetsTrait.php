<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait AudienceTargetsTrait
{
    /**
     * @var Collection<int, Grade>
     */
    #[ORM\ManyToMany(targetEntity: Grade::class)]
    #[Groups(['faq:read', 'post:read', 'event:read'])]
    private Collection $grades;

    /**
     * @var Collection<int, SchoolClass>
     */
    #[ORM\ManyToMany(targetEntity: SchoolClass::class)]
    #[Groups(['faq:read', 'post:read', 'event:read'])]
    private Collection $schoolClasses;

    private function initializeAudienceTargets(): void
    {
        $this->grades = new ArrayCollection();
        $this->schoolClasses = new ArrayCollection();
    }

    /**
     * @return Collection<int, Grade>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
        }

        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        $this->grades->removeElement($grade);

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
        }

        return $this;
    }

    public function removeSchoolClass(SchoolClass $schoolClass): static
    {
        $this->schoolClasses->removeElement($schoolClass);

        return $this;
    }
}
