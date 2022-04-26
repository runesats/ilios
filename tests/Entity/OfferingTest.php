<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Course;
use App\Entity\Offering;
use App\Entity\School;
use App\Entity\Session;
use App\Entity\SessionInterface;
use DateTime;
use Mockery as m;

/**
 * Tests for Entity Offering
 * @group model
 */
class OfferingTest extends EntityBase
{
    /**
     * @var Offering
     */
    protected $object;

    /**
     * Instantiate a Offering object
     */
    protected function setUp(): void
    {
        $this->object = new Offering();
    }

    public function testNotBlankValidation()
    {
        $notBlank = [
            'startDate',
            'endDate'
        ];
        $this->object->setSession(m::mock(SessionInterface::class));

        $this->validateNotBlanks($notBlank);

        $this->object->setStartDate(new DateTime());
        $this->object->setEndDate(new DateTime());
        $this->object->setRoom('');
        $this->object->setSite('');
        $this->validate(0);
        $this->object->setRoom('test');
        $this->object->setSite('test');
        $this->validate(0);
    }

    public function testNotNullValidation()
    {
        $notNulls = [
            'session'
        ];

        $this->object->setRoom('RCF 112');
        $this->object->setStartDate(new DateTime());
        $this->object->setEndDate(new DateTime());

        $this->validateNotNulls($notNulls);
        $this->object->setSession(m::mock('App\Entity\SessionInterface'));

        $this->validate(0);
    }

    /**
     * @covers \App\Entity\Offering::__construct
     */
    public function testConstructor()
    {
        $this->assertEmpty($this->object->getLearnerGroups());
        $this->assertEmpty($this->object->getInstructorGroups());
        $this->assertEmpty($this->object->getLearners());
        $this->assertEmpty($this->object->getInstructors());
        $this->assertNotEmpty($this->object->getUpdatedAt());
    }

    /**
     * @covers \App\Entity\Offering::setRoom
     * @covers \App\Entity\Offering::getRoom
     */
    public function testSetRoom()
    {
        $this->basicSetTest('room', 'string');
    }

    /**
     * @covers \App\Entity\Offering::setSite
     * @covers \App\Entity\Offering::getSite
     */
    public function testSetSite()
    {
        $this->basicSetTest('site', 'string');
    }

    /**
     * @covers \App\Entity\Offering::setUrl
     * @covers \App\Entity\Offering::getUrl
     */
    public function testSetUrl()
    {
        $this->basicSetTest('url', 'string');
    }

    public function testValidateUrl()
    {
        $this->object->setUrl('something');
        $errors = $this->validate(4);
        $this->assertTrue(
            array_key_exists('url', $errors),
            "url key not found in errors: " . var_export(array_keys($errors), true)
        );
        $this->assertSame('Url', $errors['url']);

        $this->object->setUrl('http://example.edu');
        $this->validate(3);

        $this->object->setUrl(null);
        $this->validate(3);
    }

    /**
     * @covers \App\Entity\Offering::setStartDate
     * @covers \App\Entity\Offering::getStartDate
     */
    public function testSetStartDate()
    {
        $this->basicSetTest('startDate', 'datetime');
    }

    /**
     * @covers \App\Entity\Offering::setEndDate
     * @covers \App\Entity\Offering::getEndDate
     */
    public function testSetEndDate()
    {
        $this->basicSetTest('endDate', 'datetime');
    }

    /**
     * @covers \App\Entity\Offering::setSession
     * @covers \App\Entity\Offering::getSession
     */
    public function testSetSession()
    {
        $this->entitySetTest('session', 'Session');
    }

    /**
     * @covers \App\Entity\Offering::addLearnerGroup
     */
    public function testAddLearnerGroup()
    {
        $this->entityCollectionAddTest('learnerGroup', 'LearnerGroup');
    }

    /**
     * @covers \App\Entity\Offering::removeLearnerGroup
     */
    public function testRemoveLearnerGroup()
    {
        $this->entityCollectionRemoveTest('learnerGroup', 'LearnerGroup');
    }

    /**
     * @covers \App\Entity\Offering::setLearnerGroups
     */
    public function testSetLearnerGroup()
    {
        $this->entityCollectionSetTest('learnerGroup', 'LearnerGroup');
    }

    /**
     * @covers \App\Entity\Offering::addInstructorGroup
     */
    public function testAddInstructorGroup()
    {
        $this->entityCollectionAddTest('instructorGroup', 'InstructorGroup');
    }

    /**
     * @covers \App\Entity\Offering::removeInstructorGroup
     */
    public function testRemoveInstructorGroup()
    {
        $this->entityCollectionRemoveTest('instructorGroup', 'InstructorGroup');
    }

    /**
     * @covers \App\Entity\Offering::setInstructorGroups
     */
    public function testSetInstructorGroup()
    {
        $this->entityCollectionSetTest('instructorGroup', 'InstructorGroup');
    }

    /**
     * @covers \App\Entity\Offering::addLearner
     */
    public function testAddLearner()
    {
        $this->entityCollectionAddTest('learner', 'User');
    }

    /**
     * @covers \App\Entity\Offering::removeLearner
     */
    public function testRemoveLearner()
    {
        $this->entityCollectionRemoveTest('learner', 'User');
    }

    /**
     * @covers \App\Entity\Offering::setLearners
     */
    public function testSetLearner()
    {
        $this->entityCollectionSetTest('learner', 'User');
    }

    /**
     * @covers \App\Entity\Offering::addInstructor
     */
    public function testAddInstructor()
    {
        $this->entityCollectionAddTest('instructor', 'User');
    }

    /**
     * @covers \App\Entity\Offering::removeInstructor
     */
    public function testRemoveInstructor()
    {
        $this->entityCollectionRemoveTest('instructor', 'User');
    }

    /**
     * @covers \App\Entity\Offering::setInstructors
     */
    public function testSetInstructor()
    {
        $this->entityCollectionSetTest('instructor', 'User');
    }

    /**
     * @covers \App\Entity\Offering::getSchool
     */
    public function testGetSchool()
    {
        $school = new School();
        $course = new Course();
        $course->setSchool($school);
        $session = new Session();
        $session->setCourse($course);
        $offering = new Offering();
        $offering->setSession($session);
        $this->assertSame($school, $offering->getSchool());

        $course = new Course();
        $session = new Session();
        $session->setCourse($course);
        $offering = new Offering();
        $offering->setSession($session);
        $this->assertNull($offering->getSchool());

        $session = new Session();
        $offering = new Offering();
        $offering->setSession($session);
        $this->assertNull($offering->getSchool());

        $offering = new Offering();
        $this->assertNull($offering->getSchool());
    }
}
