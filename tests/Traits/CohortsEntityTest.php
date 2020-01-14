<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Cohort;
use App\Traits\CohortsEntity;
use Mockery as m;
use App\Tests\TestCase;

/**
 * @coversDefaultClass \App\Traits\CohortsEntity
 */

class CohortsEntityTest extends TestCase
{
    /**
     * @var CohortsEntity
     */
    private $traitObject;
    public function setUp()
    {
        $traitName = CohortsEntity::class;
        $this->traitObject = $this->getObjectForTrait($traitName);
    }

    public function tearDown(): void
    {
        unset($this->object);
    }

    /**
     * @covers ::setCohorts
     */
    public function testSetCohorts()
    {
        $collection = new ArrayCollection();
        $collection->add(m::mock(Cohort::class));
        $collection->add(m::mock(Cohort::class));
        $collection->add(m::mock(Cohort::class));

        $this->traitObject->setCohorts($collection);
        $this->assertEquals($collection, $this->traitObject->getCohorts());
    }

    /**
     * @covers ::removeCohort
     */
    public function testRemoveCohort()
    {
        $collection = new ArrayCollection();
        $one = m::mock(Cohort::class);
        $two = m::mock(Cohort::class);
        $collection->add($one);
        $collection->add($two);

        $this->traitObject->setCohorts($collection);
        $this->traitObject->removeCohort($one);
        $cohorts = $this->traitObject->getCohorts();
        $this->assertEquals(1, $cohorts->count());
        $this->assertEquals($two, $cohorts->first());
    }
}
