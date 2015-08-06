<?php

namespace Ilios\AuthenticationBundle\Voter;

use Ilios\CoreBundle\Entity\CourseLearningMaterialInterface;
use Ilios\CoreBundle\Entity\UserInterface;

/**
 * Class SessionVoter
 * @package Ilios\AuthenticationBundle\Voter
 */
class CourseLearningMaterialVoter extends CourseVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Ilios\CoreBundle\Entity\CourseLearningMaterialInterface');
    }

    /**
     * @param string $attribute
     * @param CourseLearningMaterialInterface $material
     * @param UserInterface|null $user
     * @return bool
     */
    protected function isGranted($attribute, $material, $user = null)
    {
        // grant perms based on the owning session
        return parent::isGranted($attribute, $material->getCourse(), $user);
    }

    /**
     * {@inheritdoc}
     */
    protected function isCreateGranted($course, $user)
    {
        return parent::isEditGranted($course, $user);
    }
}
