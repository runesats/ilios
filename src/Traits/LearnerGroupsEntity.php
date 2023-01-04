<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\LearnerGroupInterface;

/**
 * Class LearnerGroupsEntity
 */
trait LearnerGroupsEntity
{
    protected Collection $learnerGroups;

    public function setLearnerGroups(Collection $learnerGroups)
    {
        $this->learnerGroups = new ArrayCollection();

        foreach ($learnerGroups as $learnerGroup) {
            $this->addLearnerGroup($learnerGroup);
        }
    }

    public function addLearnerGroup(LearnerGroupInterface $learnerGroup)
    {
        if (!$this->learnerGroups->contains($learnerGroup)) {
            $this->learnerGroups->add($learnerGroup);
        }
    }

    public function removeLearnerGroup(LearnerGroupInterface $learnerGroup)
    {
        $this->learnerGroups->removeElement($learnerGroup);
    }

    public function getLearnerGroups(): Collection
    {
        return $this->learnerGroups;
    }
}
