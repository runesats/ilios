<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\InstructorGroupInterface;

/**
 * Class InstructorGroupsEntity
 */
trait InstructorGroupsEntity
{
    protected Collection $instructorGroups;

    public function setInstructorGroups(Collection $instructorGroups)
    {
        $this->instructorGroups = new ArrayCollection();

        foreach ($instructorGroups as $instructorGroup) {
            $this->addInstructorGroup($instructorGroup);
        }
    }

    public function addInstructorGroup(InstructorGroupInterface $instructorGroup)
    {
        if (!$this->instructorGroups->contains($instructorGroup)) {
            $this->instructorGroups->add($instructorGroup);
        }
    }

    public function removeInstructorGroup(InstructorGroupInterface $instructorGroup)
    {
        $this->instructorGroups->removeElement($instructorGroup);
    }

    public function getInstructorGroups(): Collection
    {
        return $this->instructorGroups;
    }
}
