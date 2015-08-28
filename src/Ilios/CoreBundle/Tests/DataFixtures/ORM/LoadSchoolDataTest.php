<?php

namespace Ilios\CoreBundle\Tests\DataFixtures\ORM;

use Ilios\CoreBundle\Entity\Manager\SchoolManagerInterface;
use Ilios\CoreBundle\Entity\SchoolInterface;

/**
 * Class LoadSchoolDataTest
 * @package Ilios\CoreBundle\Tests\DataFixtures\ORM
 */
class LoadSchoolDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getDataFileName()
    {
        return 'school.csv';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'ilioscore.school.manager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            'Ilios\CoreBundle\DataFixtures\ORM\LoadSchoolData',
        ];
    }

    /**
     * @param array $data
     * @param SchoolInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `school_id`,`template_prefix`,`title`,`ilios_administrator_email`,`deleted`,`change_alert_recipients`
        $this->assertEquals($data[0], $entity->getId());
        $this->assertEquals($data[1], $entity->getTemplatePrefix());
        $this->assertEquals($data[2], $entity->getTitle());
        $this->assertEquals($data[3], $entity->getIliosAdministratorEmail());
        $this->assertEquals((boolean) $data[4], $entity->isDeleted());
        $this->assertEquals($data[5], $entity->getChangeAlertRecipients());
    }

    /**
     * @param array $data
     * @return SchoolInterface
     * @override
     */
    protected function getEntity(array $data)
    {
        /**
         * @var SchoolManagerInterface $em
         */
        $em = $this->em;
        return $em->findSchoolBy(['id' => $data[0]]);
    }
}
