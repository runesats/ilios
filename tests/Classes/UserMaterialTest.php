<?php

declare(strict_types=1);

namespace App\Tests\Classes;

use App\Classes\UserMaterial;
use App\Tests\TestCase;
use DateTime;

/**
 * Class UserMaterialTest
 * @package App\Tests\Classes
 * @covers \App\Classes\UserMaterial
 * @coversDefaultClass \App\Classes\UserMaterial
 */
class UserMaterialTest extends TestCase
{
    protected UserMaterial $userMaterial;

    protected function setUp(): void
    {
        $this->userMaterial = new UserMaterial();
        $this->userMaterial->id = 1;
        $this->userMaterial->courseLearningMaterial = 1;
        $this->userMaterial->sessionLearningMaterial = 1;
        $this->userMaterial->position = 1;
        $this->userMaterial->session = 1;
        $this->userMaterial->course = 1;
        $this->userMaterial->publicNotes = 'Notes';
        $this->userMaterial->required = true;
        $this->userMaterial->title = 'My Material';
        $this->userMaterial->description = 'Lorem Ipsum';
        $this->userMaterial->originalAuthor = 'Randy Bobandy';
        $this->userMaterial->absoluteFileUri = 'http://localhost/test.txt';
        $this->userMaterial->citation = 'Citation';
        $this->userMaterial->link = 'http://127.0.0.1';
        $this->userMaterial->filename = 'test.txt';
        $this->userMaterial->filesize = 1000;
        $this->userMaterial->mimetype = 'plain/text';
        $this->userMaterial->sessionTitle = 'Session 1';
        $this->userMaterial->courseTitle = 'Course 1';
        $this->userMaterial->courseExternalId = 'ID1234';
        $this->userMaterial->courseYear = 2022;
        $this->userMaterial->firstOfferingDate = new DateTime();
        $this->userMaterial->instructors = [ 1, 2, 3];
        $this->userMaterial->isBlanked = false;
        $this->userMaterial->startDate = null;
        $this->userMaterial->endDate = null;
    }

    protected function tearDown(): void
    {
        unset($this->userMaterial);
    }


    /**
     * @covers ::clearMaterial
     */
    public function testClearMaterial()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->clearMaterial();
        $this->assertBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithNoDates()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertNotBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithStartDateAndInRange()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->startDate = new DateTime('2 days ago');
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertNotBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithStartDateAndOutOfRange()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->startDate = new DateTime('+2 days');
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithEndDateAndInRange()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->endDate = new DateTime('+2 days');
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertNotBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithEndDateAndOutOfRange()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->endDate = new DateTime('2 days ago');
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithStartEndDateAndInRange()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->startDate = new DateTime('2 days ago');
        $this->userMaterial->endDate = new DateTime('+2 days');
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertNotBlanked($this->userMaterial);
    }

    /**
     * @covers ::clearTimedMaterial
     */
    public function testClearTimedMaterialWithStartEndDateAndOutOfRange()
    {
        $this->assertNotBlanked($this->userMaterial);
        $this->userMaterial->startDate = new DateTime('4 days ago');
        $this->userMaterial->endDate = new DateTime('2 days ago');
        $this->userMaterial->clearTimedMaterial(new DateTime());
        $this->assertBlanked($this->userMaterial);
    }

    /**
     * @param UserMaterial $material
     */
    protected function assertBlanked(UserMaterial $material)
    {
        $this->assertTrue($material->isBlanked);
        $this->assertNotNull($material->id);
        $this->assertNotNull($material->courseLearningMaterial);
        $this->assertNotNull($material->sessionLearningMaterial);
        $this->assertNotNull($material->position);
        $this->assertNotNull($material->title);
        $this->assertNotNull($material->session);
        $this->assertNotNull($material->sessionTitle);
        $this->assertNotNull($material->course);
        $this->assertNotNull($material->courseTitle);
        $this->assertNotNull($material->courseExternalId);
        $this->assertNotNull($material->courseYear);
        $this->assertNotNull($material->firstOfferingDate);
        $this->assertNull($material->publicNotes);
        $this->assertNull($material->required);
        $this->assertNull($material->description);
        $this->assertNull($material->originalAuthor);
        $this->assertNull($material->absoluteFileUri);
        $this->assertNull($material->citation);
        $this->assertNull($material->link);
        $this->assertNull($material->filename);
        $this->assertNull($material->filesize);
        $this->assertNull($material->mimetype);
        $this->assertEmpty($material->instructors);
    }

    /**
     * @param UserMaterial $material
     */
    protected function assertNotBlanked(UserMaterial $material)
    {
        $this->assertFalse($material->isBlanked);
        $this->assertNotNull($material->id);
        $this->assertNotNull($material->courseLearningMaterial);
        $this->assertNotNull($material->sessionLearningMaterial);
        $this->assertNotNull($material->position);
        $this->assertNotNull($material->title);
        $this->assertNotNull($material->session);
        $this->assertNotNull($material->sessionTitle);
        $this->assertNotNull($material->course);
        $this->assertNotNull($material->courseTitle);
        $this->assertNotNull($material->courseExternalId);
        $this->assertNotNull($material->courseYear);
        $this->assertNotNull($material->firstOfferingDate);
        $this->assertNotNull($material->publicNotes);
        $this->assertNotNull($material->required);
        $this->assertNotNull($material->description);
        $this->assertNotNull($material->originalAuthor);
        $this->assertNotNull($material->absoluteFileUri);
        $this->assertNotNull($material->citation);
        $this->assertNotNull($material->link);
        $this->assertNotNull($material->filename);
        $this->assertNotNull($material->filesize);
        $this->assertNotNull($material->mimetype);
        $this->assertNotEmpty($material->instructors);
    }
}
