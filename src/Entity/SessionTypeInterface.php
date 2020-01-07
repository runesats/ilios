<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Traits\ActivatableEntityInterface;
use App\Traits\IdentifiableEntityInterface;
use App\Traits\SchoolEntityInterface;
use App\Traits\TitledEntityInterface;
use App\Traits\SessionsEntityInterface;

/**
 * Interface SessionTypeInterface
 */
interface SessionTypeInterface extends
    IdentifiableEntityInterface,
    TitledEntityInterface,
    SessionsEntityInterface,
    SchoolEntityInterface,
    ActivatableEntityInterface,
    LoggableEntityInterface
{
    /**
     * @param string $color
     */
    public function setCalendarColor($color);

    /**
     * @return string
     */
    public function getCalendarColor();

    /**
     * Set assessment
     *
     * @param bool $assessment
     */
    public function setAssessment($assessment);

    /**
     * Get assessment
     *
     * @return bool
     */
    public function isAssessment();

    /**
     * @param AssessmentOptionInterface $assessmentOption
     */
    public function setAssessmentOption(AssessmentOptionInterface $assessmentOption = null);

    /**
     * @return AssessmentOptionInterface
     */
    public function getAssessmentOption();

    /**
     * @param Collection $aamcMethods
     */
    public function setAamcMethods(Collection $aamcMethods);

    /**
     * @param AamcMethodInterface $aamcMethod
     */
    public function addAamcMethod(AamcMethodInterface $aamcMethod);

    /**
     * @param AamcMethodInterface $aamcMethod
     */
    public function removeAamcMethod(AamcMethodInterface $aamcMethod);

    /**
     * @return ArrayCollection|AamcMethodInterface[]
     */
    public function getAamcMethods();
}
