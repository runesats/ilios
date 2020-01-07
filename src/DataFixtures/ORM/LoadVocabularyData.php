<?php

namespace App\DataFixtures\ORM;

use App\Service\DataimportFileLocator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Vocabulary;
use App\Entity\VocabularyInterface;

/**
 * Class LoadVocabularyData
 */
class LoadVocabularyData extends AbstractFixture implements DependentFixtureInterface
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'vocabulary');
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'App\DataFixtures\ORM\LoadSchoolData',
        ];
    }

    /**
     * @return VocabularyInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new Vocabulary();
    }

    /**
     * @param VocabularyInterface $entity
     * @param array $data
     * @return VocabularyInterface
     *
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `vocabulary_id`,`title`,`school_id`, `active`
        $entity->setId($data[0]);
        $entity->setTitle($data[1]);
        $entity->setSchool($this->getReference('school' . $data[2]));
        $entity->setActive((bool) $data[3]);
        return $entity;
    }
}
