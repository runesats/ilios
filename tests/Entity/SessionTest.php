<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Course;
use App\Entity\CourseInterface;
use App\Entity\School;
use App\Entity\Session;
use App\Entity\SessionTypeInterface;
use Mockery as m;

/**
 * Tests for Entity Session
 * @group model
 */
class SessionTest extends EntityBase
{
    /**
     * @var Session
     */
    protected $object;

    /**
     * Instantiate a Session object
     */
    protected function setUp(): void
    {
        $this->object = new Session();
    }

    public function testNotBlankValidation()
    {
        $notBlank = [

        ];
        $this->object->setSessionType(m::mock(SessionTypeInterface::class));
        $this->object->setCourse(m::mock(CourseInterface::class));

        $this->validateNotBlanks($notBlank);
        $this->validate(0);
    }

    public function testNotNullValidation()
    {
        $notNull = [
            'sessionType',
            'course'
        ];
        $this->validateNotNulls($notNull);

        $this->object->setSessionType(m::mock('App\Entity\SessionTypeInterface'));
        $this->object->setCourse(m::mock('App\Entity\CourseInterface'));

        $this->validate(0);
    }
    /**
     * @covers \App\Entity\Session::__construct
     */
    public function testConstructor()
    {
        $this->assertEmpty($this->object->getMeshDescriptors());
        $this->assertEmpty($this->object->getSessionObjectives());
        $this->assertEmpty($this->object->getOfferings());
        $this->assertEmpty($this->object->getTerms());
        $this->assertEmpty($this->object->getSequenceBlocks());
        $this->assertEmpty($this->object->getPrerequisites());
        $this->assertEmpty($this->object->getAdministrators());
        $this->assertEmpty($this->object->getStudentAdvisors());
    }

    /**
     * @covers \App\Entity\Session::setTitle
     * @covers \App\Entity\Session::getTitle
     */
    public function testSetTitle()
    {
        $this->basicSetTest('title', 'string');
    }

    /**
     * @covers \App\Entity\Session::setDescription
     * @covers \App\Entity\Session::getDescription
     */
    public function testSetDescription()
    {
        $description = 'lorem ipsum';
        $this->object->setDescription($description);
        $this->assertEquals($description, $this->object->getDescription());
    }

    /**
     * @covers \App\Entity\Session::setAttireRequired
     * @covers \App\Entity\Session::isAttireRequired
     */
    public function testSetAttireRequired()
    {
        $this->booleanSetTest('attireRequired');
    }

    /**
     * @covers \App\Entity\Session::setEquipmentRequired
     * @covers \App\Entity\Session::isEquipmentRequired
     */
    public function testSetEquipmentRequired()
    {
        $this->booleanSetTest('equipmentRequired');
    }

    /**
     * @covers \App\Entity\Session::setSupplemental
     * @covers \App\Entity\Session::isSupplemental
     */
    public function testSetSupplemental()
    {
        $this->booleanSetTest('supplemental');
    }

    /**
     * @covers \App\Entity\Session::setAttendanceRequired
     * @covers \App\Entity\Session::isAttendanceRequired
     */
    public function testSetAttendanceRequired()
    {
        $this->booleanSetTest('attendanceRequired');
    }

    /**
     * @covers \App\Entity\Session::setPublishedAsTbd
     * @covers \App\Entity\Session::isPublishedAsTbd
     */
    public function testSetPublishedAsTbd()
    {
        $this->booleanSetTest('publishedAsTbd');
    }

    /**
     * @covers \App\Entity\Session::setPublished
     * @covers \App\Entity\Session::isPublished
     */
    public function testSetPublished()
    {
        $this->booleanSetTest('published');
    }

    /**
     * @covers \App\Entity\Session::setInstructionalNotes
     * @covers \App\Entity\Session::getInstructionalNotes
     */
    public function testSetInstructionalNotes()
    {
        $this->basicSetTest('instructionalNotes', 'string');
    }

    /**
     * @covers \App\Entity\Session::setSessionType
     * @covers \App\Entity\Session::getSessionType
     */
    public function testSetSessionType()
    {
        $this->entitySetTest('sessionType', "SessionType");
    }

    /**
     * @covers \App\Entity\Session::setCourse
     * @covers \App\Entity\Session::getCourse
     */
    public function testSetCourse()
    {
        $this->entitySetTest('course', "Course");
    }

    /**
     * @covers \App\Entity\Session::setIlmSession
     * @covers \App\Entity\Session::getIlmSession
     */
    public function testSetIlmSession()
    {
        $this->assertTrue(method_exists($this->object, 'getIlmSession'), "Method getIlmSession missing");
        $this->assertTrue(method_exists($this->object, 'setIlmSession'), "Method setIlmSession missing");
        $obj = m::mock('App\Entity\IlmSession');
        $obj->shouldReceive('setSession')->with($this->object)->once();
        $this->object->setIlmSession($obj);
        $this->assertSame($obj, $this->object->getIlmSession());
    }

    /**
     * @covers \App\Entity\Session::addLearningMaterial
     */
    public function testAddLearningMaterial()
    {
        $this->entityCollectionAddTest('learningMaterial', 'SessionLearningMaterial');
    }

    /**
     * @covers \App\Entity\Session::removeLearningMaterial
     */
    public function testRemoveLearningMaterial()
    {
        $this->entityCollectionRemoveTest('learningMaterial', 'SessionLearningMaterial');
    }

    /**
     * @covers \App\Entity\Session::setLearningMaterials
     * @covers \App\Entity\Session::getLearningMaterials
     */
    public function testGetLearningMaterials()
    {
        $this->entityCollectionSetTest('learningMaterial', 'SessionLearningMaterial');
    }

    /**
     * @covers \App\Entity\Session::getSchool
     */
    public function testGetSchool()
    {
        $school = new School();
        $course = new Course();
        $session = new Session();
        $course->setSchool($school);
        $session->setCourse($course);
        $this->assertSame($school, $session->getSchool());

        $course = new Course();
        $session = new Session();
        $session->setCourse($course);
        $this->assertNull($session->getSchool());

        $session = new Session();
        $this->assertNull($session->getSchool());
    }

    /**
     * @covers \App\Entity\Session::addTerm
     */
    public function testAddTerm()
    {
        $this->entityCollectionAddTest('term', 'Term');
    }

    /**
     * @covers \App\Entity\Session::removeTerm
     */
    public function testRemoveTerm()
    {
        $this->entityCollectionRemoveTest('term', 'Term');
    }

    /**
     * @covers \App\Entity\Session::getTerms
     * @covers \App\Entity\Session::setTerms
     */
    public function testSetTerms()
    {
        $this->entityCollectionSetTest('term', 'Term');
    }

    /**
     * @covers \App\Entity\Session::addSessionObjective
     */
    public function testAddSessionObjective()
    {
        $this->entityCollectionAddTest('sessionObjective', 'SessionObjective');
    }

    /**
     * @covers \App\Entity\Session::removeSessionObjective
     */
    public function testRemoveSessionObjective()
    {
        $this->entityCollectionRemoveTest('sessionObjective', 'SessionObjective');
    }

    /**
     * @covers \App\Entity\Session::setSessionObjectives
     * @covers \App\Entity\Session::getSessionObjectives
     */
    public function testGetSessionObjectives()
    {
        $this->entityCollectionSetTest('sessionObjective', 'SessionObjective');
    }

    /**
     * @covers \App\Entity\Session::addMeshDescriptor
     */
    public function testAddMeshDescriptor()
    {
        $this->entityCollectionAddTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\Session::removeMeshDescriptor
     */
    public function testRemoveMeshDescriptor()
    {
        $this->entityCollectionRemoveTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\Session::getMeshDescriptors
     * @covers \App\Entity\Session::setMeshDescriptors
     */
    public function testSetMeshDescriptors()
    {
        $this->entityCollectionSetTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\Session::addSequenceBlock
     */
    public function testAddSequenceBlock()
    {
        $this->entityCollectionAddTest('sequenceBlock', 'CurriculumInventorySequenceBlock');
    }

    /**
     * @covers \App\Entity\Session::removeSequenceBlock
     */
    public function testRemoveSequenceBlock()
    {
        $this->entityCollectionRemoveTest('sequenceBlock', 'CurriculumInventorySequenceBlock');
    }

    /**
     * @covers \App\Entity\Session::setSequenceBlocks
     * @covers \App\Entity\Session::getSequenceBlocks
     */
    public function testGetSequenceBlocks()
    {
        $this->entityCollectionSetTest('sequenceBlock', 'CurriculumInventorySequenceBlock');
    }

    /**
     * @covers \App\Entity\Session::addOffering
     */
    public function testAddOffering()
    {
        $this->entityCollectionAddTest('offering', 'Offering');
    }

    /**
     * @covers \App\Entity\Session::removeOffering
     */
    public function testRemoveOffering()
    {
        $this->entityCollectionRemoveTest('offering', 'Offering');
    }

    /**
     * @covers \App\Entity\Session::getOfferings
     * @covers \App\Entity\Session::setOfferings
     */
    public function testSetOfferings()
    {
        $this->entityCollectionSetTest('offering', 'Offering');
    }

    /**
     * @covers \App\Entity\Session::addAdministrator
     */
    public function testAddAdministrator()
    {
        $this->entityCollectionAddTest('administrator', 'User', false, false, 'addAdministeredSession');
    }

    /**
     * @covers \App\Entity\Session::removeAdministrator
     */
    public function testRemoveAdministrator()
    {
        $this->entityCollectionRemoveTest('administrator', 'User', false, false, false, 'removeAdministeredSession');
    }

    /**
     * @covers \App\Entity\Session::getAdministrators
     * @covers \App\Entity\Session::setAdministrators
     */
    public function testSetAdministrators()
    {
        $this->entityCollectionSetTest('administrator', 'User', false, false, 'addAdministeredSession');
    }

    /**
     * @covers \App\Entity\Session::addStudentAdvisor
     */
    public function testAddStudentAdvisor()
    {
        $this->entityCollectionAddTest('studentAdvisor', 'User', false, false, 'addStudentAdvisedSession');
    }

    /**
     * @covers \App\Entity\Session::removeStudentAdvisor
     */
    public function testRemoveStudentAdvisor()
    {
        $this->entityCollectionRemoveTest('studentAdvisor', 'User', false, false, false, 'removeStudentAdvisedSession');
    }

    /**
     * @covers \App\Entity\Session::getStudentAdvisors
     * @covers \App\Entity\Session::setStudentAdvisors
     */
    public function testSetStudentAdvisors()
    {
        $this->entityCollectionSetTest('studentAdvisor', 'User', false, false, 'addStudentAdvisedSession');
    }

    /**
     * @covers \App\Entity\Session::addExcludedSequenceBlock
     */
    public function testAddExcludedSequenceBlock()
    {
        $this->entityCollectionAddTest('excludedSequenceBlock', 'CurriculumInventorySequenceBlock');
    }

    /**
     * @covers \App\Entity\Session::removeExcludedSequenceBlock
     */
    public function testRemoveExcludedSequenceBlock()
    {
        $this->entityCollectionRemoveTest('excludedSequenceBlock', 'CurriculumInventorySequenceBlock');
    }

    /**
     * @covers \App\Entity\Session::setExcludedSequenceBlocks
     * @covers \App\Entity\Session::getExcludedSequenceBlocks
     */
    public function testGetExcludedSequenceBlocks()
    {
        $this->entityCollectionSetTest('excludedSequenceBlock', 'CurriculumInventorySequenceBlock');
    }

    /**
     * @covers \App\Entity\Session::setPostrequisite
     * @covers \App\Entity\Session::getPostrequisite
     */
    public function testSetPostrequisite()
    {
        $this->entitySetTest('postrequisite', 'Session');
    }

    /**
     * @covers \App\Entity\Session::addPrerequisite
     */
    public function testAddPrerequisite()
    {
        $this->entityCollectionAddTest('prerequisite', 'Session', false, false, 'setPostrequisite');
    }

    /**
     * @covers \App\Entity\Session::removePrerequisite
     */
    public function testRemovePrerequisite()
    {
        $this->entityCollectionRemoveTest('prerequisite', 'Session');
    }

    /**
     * @covers \App\Entity\Session::getPrerequisites
     * @covers \App\Entity\Session::setPrerequisites
     */
    public function testGetPrerequisites()
    {
        $this->entityCollectionSetTest('prerequisite', 'Session', false, false, 'setPostrequisite');
    }

    /**
     * @covers \App\Entity\Session::getIndexableCourses
     */
    public function testGetIndexableCourses()
    {
        $course = m::mock(CourseInterface::class);
        $this->object->setCourse($course);


        $rhett = $this->object->getIndexableCourses();
        $this->assertEquals([$course], $rhett);
    }
}
