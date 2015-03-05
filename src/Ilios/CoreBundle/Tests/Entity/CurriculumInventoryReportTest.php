<?php
namespace Ilios\CoreBundle\Tests\Entity;

use Ilios\CoreBundle\Entity\CurriculumInventoryReport;
use Mockery as m;

/**
 * Tests for Entity CurriculumInventoryReport
 */
class CurriculumInventoryReportTest extends EntityBase
{
    /**
     * @var CurriculumInventoryReport
     */
    protected $object;

    /**
     * Instantiate a CurriculumInventoryReport object
     */
    protected function setUp()
    {
        $this->object = new CurriculumInventoryReport;
    }

    public function testNotBlankValidation()
    {
        $notBlank = array(
            'year',
            'startDate',
            'endDate'
        );
        $this->validateNotBlanks($notBlank);

        $this->object->setYear(2001);
        $this->object->setStartDate(new \DateTime());
        $this->object->setEndDate(new \DateTime());
        $this->validate(0);
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setYear
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getYear
     */
    public function testSetYear()
    {
        $this->basicSetTest('year', 'integer');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setName
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getName
     */
    public function testSetName()
    {
        $this->basicSetTest('name', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setDescription
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getDescription
     */
    public function testSetDescription()
    {
        $this->basicSetTest('description', 'string');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setStartDate
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getStartDate
     */
    public function testSetStartDate()
    {
        $this->basicSetTest('startDate', 'datetime');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setEndDate
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getEndDate
     */
    public function testSetEndDate()
    {
        $this->basicSetTest('endDate', 'datetime');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setExport
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getExport
     */
    public function testSetExport()
    {
        $this->entitySetTest('export', 'CurriculumInventoryExport');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setSequence
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getSequence
     */
    public function testSetSequence()
    {
        $this->entitySetTest('sequence', 'CurriculumInventorySequence');
    }

    /**
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::setProgram
     * @covers Ilios\CoreBundle\Entity\CurriculumInventoryReport::getProgram
     */
    public function testSetProgram()
    {
        $this->entitySetTest('program', 'Program');
    }
}
