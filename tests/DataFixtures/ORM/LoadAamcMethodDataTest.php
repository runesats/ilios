<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures\ORM;

use App\Entity\AamcMethodInterface;
use App\Repository\AamcMethodRepository;

/**
 * Class LoadAamcMethodDataTest
 */
class LoadAamcMethodDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return AamcMethodRepository::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            'App\DataFixtures\ORM\LoadAamcMethodData',
        ];
    }

    /**
     * @covers \App\DataFixtures\ORM\LoadAamcMethodData::load
     */
    public function testLoad()
    {
        $this->runTestLoad('aamc_method.csv');
    }

    /**
     * @param array $data
     * @param AamcMethodInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `method_id`,`description`
        $this->assertEquals($data[0], $entity->getId());
        $this->assertEquals($data[1], $entity->getDescription());
    }
}
