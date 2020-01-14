<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures\ORM;

use App\Entity\SchoolInterface;

/**
 * Class LoadSchoolDataTest
 */
class LoadSchoolDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'App\Entity\Manager\SchoolManager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            'App\DataFixtures\ORM\LoadSchoolData',
        ];
    }

    /**
     * @covers \App\DataFixtures\ORM\LoadSchoolData::load
     */
    public function testLoad()
    {
        $this->runTestLoad('school.csv');
    }

    /**
     * @param array $data
     * @param SchoolInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `school_id`,`template_prefix`,`title`,`ilios_administrator_email`,`change_alert_recipients`
        $this->assertEquals($data[0], $entity->getId());
        $this->assertEquals($data[1], $entity->getTemplatePrefix());
        $this->assertEquals($data[2], $entity->getTitle());
        $this->assertEquals($data[3], $entity->getIliosAdministratorEmail());
        $this->assertEquals($data[4], $entity->getChangeAlertRecipients());
    }
}
