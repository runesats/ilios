<?php

namespace Ilios\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Ilios\CoreBundle\Entity\SessionType;
use Ilios\CoreBundle\Entity\SessionTypeInterface;

/**
 * Class LoadSessionTypeData
 * @package Ilios\CoreBundle\DataFixtures\ORM
 */
class LoadSessionTypeData extends AbstractFixture implements DependentFixtureInterface
{
    public function __construct()
    {
        parent::__construct('session_type');
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Ilios\CoreBundle\DataFixtures\ORM\LoadSchoolData',
            'Ilios\CoreBundle\DataFixtures\ORM\LoadAssessmentOptionData',
        ];
    }

    /**
     * @return SessionTypeInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new SessionType();
    }

    /**
     * @param SessionTypeInterface $entity
     * @param array $data
     * @return SessionTypeInterface
     *
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `session_type_id`,`title`,`school_id`,`session_type_css_class`,`assessment`,`assessment_option_id`
        $entity->setId($data[0]);
        $entity->setTitle($data[1]);
        $entity->setSchool($this->getReference('school' . $data[2]));
        $entity->setSessionTypeCssClass($data[3]);
        $entity->setAssessment((boolean) $data[4]);
        if (! empty($data[5])) {
            $entity->setAssessmentOption($this->getReference('assessment_option' . $data[5]));
        }
        return $entity;
    }
}
