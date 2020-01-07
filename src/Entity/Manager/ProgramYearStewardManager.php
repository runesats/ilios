<?php

namespace App\Entity\Manager;

use App\Entity\ProgramYearInterface;
use App\Entity\ProgramYearSteward;
use App\Entity\ProgramYearStewardInterface;
use App\Entity\SchoolInterface;
use App\Traits\SchoolEntityInterface;

/**
 * Class ProgramYearStewardManager
 */
class ProgramYearStewardManager extends BaseManager
{
    /**
     * Checks if a given entity's school (co-)stewards a given program year.
     *
     * @param int $schoolId
     * @param ProgramYearInterface $programYear
     * @return bool
     */
    public function schoolIsStewardingProgramYear(
        $schoolId,
        ProgramYearInterface $programYear
    ) {
        $criteria = ['programYear' => $programYear->getId()];
        /** @var ProgramYearSteward[] $stewards */
        $stewards = $this->findBy($criteria);
        foreach ($stewards as $steward) {
            $stewardingSchool = $steward->getSchool();
            if (
                $stewardingSchool instanceof SchoolInterface
                && $schoolId === $stewardingSchool->getId()
            ) {
                return true;
            }
        }
        return false;
    }
}
