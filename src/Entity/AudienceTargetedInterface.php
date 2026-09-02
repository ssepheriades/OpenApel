<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface AudienceTargetedInterface
{
    /**
     * @return Collection<int, Grade>
     */
    public function getGrades(): Collection;

    /**
     * @return Collection<int, SchoolClass>
     */
    public function getSchoolClasses(): Collection;
}
