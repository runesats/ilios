<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Service\DataimportFileLocator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Competency;
use App\Entity\CompetencyInterface;

/**
 * Class LoadCompetencyAamcPcrsData
 */
class LoadCompetencyAamcPcrsData extends AbstractFixture implements DependentFixtureInterface
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'competency_x_aamc_pcrs', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'App\DataFixtures\ORM\LoadAamcPcrsData',
            'App\DataFixtures\ORM\LoadCompetencyData',
        ];
    }

    /**
     * @return CompetencyInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new Competency();
    }

    /**
     * @param CompetencyInterface $entity
     * @return CompetencyInterface
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `competency_id`,`pcrs_id`
        /*
         * @var CompetencyInterface $entity
         */
        // Ignore the given entity,
        // find the previously imported competency by its reference key instead.
        $entity = $this->getReference('competency' . $data[0]);
        $entity->addAamcPcrs($this->getReference('aamc_pcrs' . $data[1]));
        return $entity;
    }
}
