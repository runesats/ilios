<?php
namespace Ilios\CoreBundle\Tests\Entity;

use Ilios\CoreBundle\Entity\Department;
use Mockery as m;

/**
 * Tests for Entity Department
 */
class DepartmentTest extends EntityBase
{
    /**
     * @var Department
     */
    protected $object;

    /**
     * Instantiate a Department object
     */
    protected function setUp()
    {
        $this->object = new Department;
    }

    public function testNotBlankValidation()
    {
        $notBlank = array(
            'title'
        );
        $this->validateNotBlanks($notBlank);

        $this->object->setTitle('test');
        $this->validate(0);
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Department::setTitle
     * @covers Ilios\CoreBundle\Entity\Department::getTitle
     */
    public function testSetTitle()
    {
        $this->basicSetTest('title', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Department::setSchool
     * @covers Ilios\CoreBundle\Entity\Department::getSchool
     */
    public function testSetSchool()
    {
        $this->softDeleteEntitySetTest('school', 'School');
    }
}
