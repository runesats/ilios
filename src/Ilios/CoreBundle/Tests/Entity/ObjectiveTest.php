<?php
namespace Ilios\CoreBundle\Tests\Entity;

use Ilios\CoreBundle\Entity\Objective;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Tests for Entity Objective
 */
class ObjectiveTest extends EntityBase
{
    /**
     * @var Objective
     */
    protected $object;

    /**
     * Instantiate a Objective object
     */
    protected function setUp()
    {
        $this->object = new Objective;
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
     * @covers Ilios\CoreBundle\Entity\Objective::setTitle
     * @covers Ilios\CoreBundle\Entity\Objective::getTitle
     */
    public function testSetTitle()
    {
        $this->basicSetTest('title', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::setCompetency
     * @covers Ilios\CoreBundle\Entity\Objective::getCompetency
     */
    public function testSetCompetency()
    {
        $this->entitySetTest('competency', 'Competency');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::addCourse
     */
    public function testAddCourse()
    {
        $this->softDeleteEntityCollectionAddTest('course', 'Course', false, false, 'addObjective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::getCourses
     */
    public function testGetCourses()
    {
        $this->softDeleteEntityCollectionSetTest('course', 'Course', false, false, 'addObjective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::addProgramYear
     */
    public function testAddProgramYear()
    {
        $this->softDeleteEntityCollectionAddTest('programYear', 'ProgramYear', false, false, 'addObjective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::getProgramYears
     */
    public function testGetProgramYears()
    {
        $this->softDeleteEntityCollectionSetTest('programYear', 'ProgramYear', false, false, 'addObjective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::addSession
     */
    public function testAddSession()
    {
        $this->softDeleteEntityCollectionAddTest('session', 'Session', false, false, 'addObjective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::getSessions
     */
    public function testGetSessions()
    {
        $this->softDeleteEntityCollectionSetTest('session', 'Session', false, false, 'addObjective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::addChild
     */
    public function testAddChild()
    {
        $this->entityCollectionAddTest('children', 'Objective', 'getChildren', 'addChild');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::getChildren
     */
    public function testGetChildren()
    {
        $this->entityCollectionSetTest('children', 'Objective', 'getChildren', 'setChildren', false);
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::addMeshDescriptor
     */
    public function testAddMeshDescriptor()
    {
        $this->entityCollectionAddTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::getMeshDescriptors
     */
    public function testGetMeshDescriptors()
    {
        $this->entityCollectionSetTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::addParent
     */
    public function testAddParent()
    {
        $this->entityCollectionAddTest('parent', 'Objective');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\Objective::getParents
     */
    public function testGetParents()
    {
        $this->entityCollectionSetTest('parent', 'Objective');
    }
}
