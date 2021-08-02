<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Service\DataimportFileLocator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\AamcResourceTypeInterface;
use App\Entity\Term;
use App\Entity\TermInterface;

/**
 * Class LoadSessionTypeAamcMethodData
 */
class LoadTermAamcResourceTypeData extends AbstractFixture implements DependentFixtureInterface
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'term_x_aamc_resource_type', false);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies()
    {
        return [
            'App\DataFixtures\ORM\LoadTermData',
            'App\DataFixtures\ORM\LoadAamcResourceTypeData',
        ];
    }

    /**
     * @return TermInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new Term();
    }

    /**
     * @param TermInterface $entity
     * @return TermInterface
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `term_id`,`resource_type_id`
        // Ignore the given entity,
        // find the previously imported session type by its reference key instead.
        /* @var TermInterface $entity */
        $entity = $this->getReference('term' . $data[0]);
        /* @var AamcResourceTypeInterface $resourceType */
        $resourceType = $this->getReference('aamc_resource_type' . $data[1]);
        $entity->addAamcResourceType($resourceType);
        return $entity;
    }
}
