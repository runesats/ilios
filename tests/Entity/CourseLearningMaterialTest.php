<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\CourseInterface;
use App\Entity\CourseLearningMaterial;
use App\Entity\LearningMaterialInterface;
use Mockery as m;

/**
 * Tests for Entity CourseLearningMaterial
 * @group model
 */
class CourseLearningMaterialTest extends EntityBase
{
    /**
     * @var CourseLearningMaterial
     */
    protected $object;

    /**
     * Instantiate a CourseLearningMaterial object
     */
    protected function setUp(): void
    {
        $this->object = new CourseLearningMaterial();
    }

    public function testNotBlankValidation()
    {
        $notNull = [
            'course',
            'learningMaterial',
        ];
        $this->validateNotNulls($notNull);

        $this->object->setCourse(m::mock(CourseInterface::class));
        $this->object->setLearningMaterial(m::mock(LearningMaterialInterface::class));
        $this->validate(0);
        $this->object->setNotes('');
        $this->validate(0);
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::__construct
     */
    public function testConstructor()
    {
        $this->assertEmpty($this->object->getMeshDescriptors());
        $this->assertFalse($this->object->hasPublicNotes());
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setNotes
     * @covers \App\Entity\CourseLearningMaterial::getNotes
     */
    public function testSetNotes()
    {
        $this->basicSetTest('notes', 'string');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setRequired
     * @covers \App\Entity\CourseLearningMaterial::isRequired
     */
    public function testSetRequired()
    {
        $this->booleanSetTest('required');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setPublicNotes
     * @covers \App\Entity\CourseLearningMaterial::hasPublicNotes
     */
    public function testSetPublicNotes()
    {
        $this->booleanSetTest('publicNotes', false);
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setCourse
     * @covers \App\Entity\CourseLearningMaterial::getCourse
     */
    public function testSetCourse()
    {
        $this->entitySetTest('course', 'Course');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setLearningMaterial
     * @covers \App\Entity\CourseLearningMaterial::getLearningMaterial
     */
    public function testSetLearningMaterial()
    {
        $this->entitySetTest('learningMaterial', 'LearningMaterial');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::addMeshDescriptor
     */
    public function testAddMeshDescriptor()
    {
        $this->entityCollectionAddTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::removeMeshDescriptor
     */
    public function testRemoveMeshDescriptor()
    {
        $this->entityCollectionRemoveTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::getMeshDescriptors
     */
    public function testGetMeshDescriptors()
    {
        $this->entityCollectionSetTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setPosition
     * @covers \App\Entity\CourseLearningMaterial::getPosition
     */
    public function testSetPosition()
    {
        $this->basicSetTest('position', 'integer');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setStartDate
     * @covers \App\Entity\CourseLearningMaterial::getStartDate
     */
    public function testSetStartDate()
    {
        $this->basicSetTest('startDate', 'datetime');
    }

    /**
     * @covers \App\Entity\CourseLearningMaterial::setEndDate
     * @covers \App\Entity\CourseLearningMaterial::getEndDate
     */
    public function testSetEndDate()
    {
        $this->basicSetTest('endDate', 'datetime');
    }
}
