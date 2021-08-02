<?php

declare(strict_types=1);

namespace App\Service\DataLoader;

use App\Entity\AamcResourceType;
use App\Entity\AamcResourceTypeInterface;
use App\Service\DataimportFileLocator;
use App\Traits\IdentifiableEntityInterface;

/**
 * Class LoadAamcResourceTypeData
 */
class LoadAamcResourceTypeData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'aamc_resource_type');
    }

    /**
     * @return AamcResourceTypeInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new AamcResourceType();
    }

    /**
     * @param AamcResourceTypeInterface $entity
     * @return IdentifiableEntityInterface
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `resource_type_id`,`title`,`description`
        $entity->setId($data[0]);
        $entity->setTitle($data[1]);
        $entity->setDescription($data[2]);
        return $entity;
    }
}
