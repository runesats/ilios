<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\UserInterface;

/**
 * Class DirectorsEntity
 */
trait DirectorsEntity
{
    public function setDirectors(Collection $directors)
    {
        $this->directors = new ArrayCollection();

        foreach ($directors as $director) {
            $this->addDirector($director);
        }
    }

    public function addDirector(UserInterface $director)
    {
        if (!$this->directors->contains($director)) {
            $this->directors->add($director);
        }
    }

    public function removeDirector(UserInterface $director)
    {
        $this->directors->removeElement($director);
    }

    /**
    * @return UserInterface[]|ArrayCollection
    */
    public function getDirectors(): Collection
    {
        return $this->directors;
    }
}
