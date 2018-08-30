<?php
namespace Tests\AppBundle\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Course;
use AppBundle\Traits\CoursesEntity;
use Mockery as m;
use Tests\AppBundle\TestCase;

/**
 * @coversDefaultClass \AppBundle\Traits\CoursesEntity
 */

class CoursesEntityTest extends TestCase
{
    /**
     * @var CoursesEntity
     */
    private $traitObject;
    public function setUp()
    {
        $traitName = CoursesEntity::class;
        $this->traitObject = $this->getObjectForTrait($traitName);
    }

    public function tearDown()
    {
        unset($this->object);
    }

    /**
     * @covers ::setCourses
     */
    public function testSetCourses()
    {
        $collection = new ArrayCollection();
        $collection->add(m::mock(Course::class));
        $collection->add(m::mock(Course::class));
        $collection->add(m::mock(Course::class));

        $this->traitObject->setCourses($collection);
        $this->assertEquals($collection, $this->traitObject->getCourses());
    }

    /**
     * @covers ::removeCourse
     */
    public function testRemoveCourse()
    {
        $collection = new ArrayCollection();
        $one = m::mock(Course::class);
        $two = m::mock(Course::class);
        $collection->add($one);
        $collection->add($two);

        $this->traitObject->setCourses($collection);
        $this->traitObject->removeCourse($one);
        $courses = $this->traitObject->getCourses();
        $this->assertEquals(1, $courses->count());
        $this->assertEquals($two, $courses->first());
    }
}
