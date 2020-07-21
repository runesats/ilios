<?php

declare(strict_types=1);

namespace App\Entity\Manager;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class CourseObjectiveManager
 * @package App\Entity\Manager
 */
class CourseObjectiveManager extends BaseManager
{
    /**
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalObjectiveCount(): int
    {
        return (int) $this->em->createQuery('SELECT COUNT(o.id) FROM App\Entity\CourseObjective o')
            ->getSingleScalarResult();
    }
}
