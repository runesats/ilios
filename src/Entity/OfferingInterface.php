<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Traits\IdentifiableEntityInterface;
use App\Traits\InstructorGroupsEntityInterface;
use App\Traits\InstructorsEntityInterface;
use App\Traits\LearnerGroupsEntityInterface;
use App\Traits\LearnersEntityInterface;
use App\Traits\StringableEntityInterface;
use App\Traits\TimestampableEntityInterface;

/**
 * Interface OfferingInterface
 */
interface OfferingInterface extends
    IdentifiableEntityInterface,
    StringableEntityInterface,
    TimestampableEntityInterface,
    LoggableEntityInterface,
    SessionStampableInterface,
    LearnerGroupsEntityInterface,
    InstructorGroupsEntityInterface,
    InstructorsEntityInterface,
    LearnersEntityInterface
{
    /**
     * @param string $room
     */
    public function setRoom($room);

    /**
     * @return string
     */
    public function getRoom();

    /**
     * @param string $site
     */
    public function setSite($site);

    /**
     * @return string
     */
    public function getSite();

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate);

    /**
     * @return \DateTime
     */
    public function getStartDate();

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate);

    /**
     * @return \DateTime
     */
    public function getEndDate();

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session);

    /**
     * @return SessionInterface|null
     */
    public function getSession();

    /**
     * Get all the instructors including those in groups
     * @return ArrayCollection
     */
    public function getAllInstructors();

    /**
     * Returns "alertable" properties in an easy to compare format.
     * @return array.
     */
    public function getAlertProperties();

    /**
     * @return SchoolInterface|null
     */
    public function getSchool();
}
