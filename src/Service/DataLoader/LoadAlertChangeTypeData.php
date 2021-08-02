<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\AlertChangeType;
use App\Entity\AlertChangeTypeInterface;
use App\Service\DataimportFileLocator;

/**
 * Class LoadAlertChangeTypeData
 */
class LoadAlertChangeTypeData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'alert_change_type');
    }

    /**
     * @return AlertChangeTypeInterface
     *
     * @see AbstractFixture::createEntity()
     */
    public function createEntity()
    {
        return new AlertChangeType();
    }

    /**
     * @param AlertChangeTypeInterface $entity
     * @return AlertChangeTypeInterface
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `alert_change_type_id`,`title`
        $entity->setId($data[0]);
        $entity->setTitle($data[1]);
        return $entity;
    }
}
