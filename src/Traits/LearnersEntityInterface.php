<?php

declare(strict_types=1);

namespace App\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\UserInterface;

/**
 * Interface LearnersEntityInterface
 */
interface LearnersEntityInterface
{
    /**
     * @param Collection $learners
     */
    public function setLearners(Collection $learners);

    /**
     * @param UserInterface $learner
     */
    public function addLearner(UserInterface $learner);

    /**
     * @param UserInterface $learner
     */
    public function removeLearner(UserInterface $learner);

    /**
    * @return UserInterface[]|ArrayCollection
    */
    public function getLearners();
}
