<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\SessionObjectiveInterface;

trait SessionObjectivesEntity
{
    protected Collection $sessionObjectives;

    public function setSessionObjectives(Collection $sessionObjectives = null): void
    {
        $this->sessionObjectives = new ArrayCollection();
        if (is_null($sessionObjectives)) {
            return;
        }

        foreach ($sessionObjectives as $sessionObjective) {
            $this->addSessionObjective($sessionObjective);
        }
    }

    public function addSessionObjective(SessionObjectiveInterface $sessionObjective): void
    {
        if (!$this->sessionObjectives->contains($sessionObjective)) {
            $this->sessionObjectives->add($sessionObjective);
        }
    }

    public function removeSessionObjective(SessionObjectiveInterface $sessionObjective): void
    {
        $this->sessionObjectives->removeElement($sessionObjective);
    }

    public function getSessionObjectives(): Collection
    {
        return $this->sessionObjectives;
    }
}
