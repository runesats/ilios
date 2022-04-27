<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\LearningMaterialInterface;
use App\Entity\SessionInterface;
use App\Entity\SessionLearningMaterial;
use Mockery as m;

/**
 * Tests for Entity SessionLearningMaterial
 * @group model
 */
class SessionLearningMaterialTest extends EntityBase
{
    /**
     * @var SessionLearningMaterial
     */
    protected $object;

    /**
     * Instantiate a SessionLearningMaterial object
     */
    protected function setUp(): void
    {
        $this->object = new SessionLearningMaterial();
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::__construct
     */
    public function testConstructor()
    {
        $this->assertEmpty($this->object->getMeshDescriptors());
    }

    public function testNotBlankValidation()
    {
        $notNull = [
            'required',
            'session',
            'learningMaterial',
        ];
        $this->validateNotNulls($notNull);

        $this->object->setRequired(false);
        $this->object->setSession(m::mock(SessionInterface::class));
        $this->object->setLearningMaterial(m::mock(LearningMaterialInterface::class));
        $this->object->setNotes('');
        $this->validate(0);
        $this->object->setNotes('test');
        $this->validate(0);
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setNotes
     * @covers \App\Entity\SessionLearningMaterial::getNotes
     */
    public function testSetNotes()
    {
        $this->basicSetTest('notes', 'string');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setRequired
     * @covers \App\Entity\SessionLearningMaterial::isRequired
     */
    public function testSetRequired()
    {
        $this->booleanSetTest('required');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setPublicNotes
     * @covers \App\Entity\SessionLearningMaterial::hasPublicNotes
     */
    public function testSetNotesArePublic()
    {
        $this->booleanSetTest('publicNotes', false);
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setSession
     * @covers \App\Entity\SessionLearningMaterial::getSession
     */
    public function testSetSession()
    {
        $this->entitySetTest('session', 'Session');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setLearningMaterial
     * @covers \App\Entity\SessionLearningMaterial::getLearningMaterial
     */
    public function testSetLearningMaterial()
    {
        $this->entitySetTest('learningMaterial', "LearningMaterial");
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::addMeshDescriptor
     */
    public function testAddMeshDescriptor()
    {
        $this->entityCollectionAddTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::removeMeshDescriptor
     */
    public function testRemoveMeshDescriptor()
    {
        $this->entityCollectionRemoveTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::getMeshDescriptors
     */
    public function testGetMeshDescriptors()
    {
        $this->entityCollectionSetTest('meshDescriptor', 'MeshDescriptor');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setPosition
     * @covers \App\Entity\SessionLearningMaterial::getPosition
     */
    public function testSetPosition()
    {
        $this->basicSetTest('position', 'integer');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setStartDate
     * @covers \App\Entity\SessionLearningMaterial::getStartDate
     */
    public function testSetStartDate()
    {
        $this->basicSetTest('startDate', 'datetime');
    }

    /**
     * @covers \App\Entity\SessionLearningMaterial::setEndDate
     * @covers \App\Entity\SessionLearningMaterial::getEndDate
     */
    public function testSetEndDate()
    {
        $this->basicSetTest('endDate', 'datetime');
    }
}
