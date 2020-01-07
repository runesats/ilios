<?php

namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\CohortInterface;

/**
 * Interface DescribableEntityInterface
 */
interface CohortsEntityInterface
{
    /**
     * @param Collection $cohorts
     */
    public function setCohorts(Collection $cohorts);

    /**
     * @param CohortInterface $cohort
     */
    public function addCohort(CohortInterface $cohort);

    /**
     * @param CohortInterface $cohort
     */
    public function removeCohort(CohortInterface $cohort);

    /**
    * @return CohortInterface[]|ArrayCollection
    */
    public function getCohorts();
}
