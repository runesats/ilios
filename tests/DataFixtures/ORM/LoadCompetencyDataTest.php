<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures\ORM;

use App\Entity\CompetencyInterface;

/**
 * Class LoadCompetencyDataTest
 */
class LoadCompetencyDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'App\Entity\Manager\CompetencyManager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            'App\DataFixtures\ORM\LoadCompetencyData',
        ];
    }

    /**
     * @covers \App\DataFixtures\ORM\LoadCompetencyData::load
     */
    public function testLoad()
    {
        $this->runTestLoad('competency.csv');
    }

    /**
     * @param array $data
     * @param CompetencyInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `competency_id`,`title`,`parent_competency_id`,`school_id`,`active`
        $this->assertEquals($data[0], $entity->getId());
        $this->assertEquals($data[1], $entity->getTitle());
        if (empty($data[2])) {
            $this->assertNull($entity->getParent());
        } else {
            $this->assertEquals($data[2], $entity->getParent()->getId());
        }
        $this->assertEquals($data[3], $entity->getSchool()->getId());
        $this->assertEquals((bool) $data[4], $entity->isActive());
    }
}
