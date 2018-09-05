<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationConfig;
use AppBundle\Entity\ApplicationConfigInterface;
use AppBundle\Service\DataimportFileLocator;

/**
 * Class LoadApplicationConfigData
 * @package AppBundle\DataFixtures\ORM
 */
class LoadApplicationConfigData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'application_config');
    }

    /**
     * @return ApplicationConfigInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new ApplicationConfig();
    }

    /**
     * @param ApplicationConfigInterface $entity
     * @param array $data
     * @return ApplicationConfigInterface
     *
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `id`, `name`,`value`
        $entity->setId($data[0]);
        $entity->setName($data[1]);
        $entity->setValue($data[2]);
        return $entity;
    }
}
