<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\School;
use App\Entity\SchoolInterface;
use App\Service\DataimportFileLocator;

/**
 * Class LoadSchoolData
 */
class LoadSchoolData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'school');
    }

    /**
     * @return SchoolInterface
     *
     * @see AbstractFixture::createEntity()
     */
    protected function createEntity()
    {
        return new School();
    }


    /**
     * @param SchoolInterface $entity
     * @return SchoolInterface
     * @see AbstractFixture::populateEntity()
     */
    protected function populateEntity($entity, array $data)
    {
        // `school_id`,`template_prefix`,`title`,`ilios_administrator_email`,`change_alert_recipients`
        $entity->setId($data[0]);
        $entity->setTemplatePrefix($data[1]);
        $entity->setTitle($data[2]);
        $entity->setIliosAdministratorEmail($data[3]);
        $entity->setChangeAlertRecipients($data[4]);
        return $entity;
    }
}
