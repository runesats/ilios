<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\AssessmentOption;
use App\Entity\AssessmentOptionInterface;
use App\Service\DataimportFileLocator;

/**
 * Class LoadAssessmentOptionData
 */
class LoadAssessmentOptionData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'assessment_option');
    }

    /**
     * @return AssessmentOptionInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new AssessmentOption();
    }

    /**
     * @param AssessmentOptionInterface $entity
     * @return AssessmentOptionInterface
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `assessment_option_id`,`name`
        $entity->setId($data[0]);
        $entity->setName($data[1]);
        return $entity;
    }
}
