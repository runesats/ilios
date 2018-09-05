<?php

namespace Tests\AppBundle\Fixture;

use AppBundle\Entity\CurriculumInventoryExport;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCurriculumInventoryExportData extends AbstractFixture implements
    ORMFixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('Tests\AppBundle\DataLoader\CurriculumInventoryExportData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new CurriculumInventoryExport();
            $entity->setId($arr['id']);
            $entity->setReport($this->getReference('curriculumInventoryReports' . $arr['report']));
            $entity->setCreatedBy($this->getReference('users' .$arr['createdBy']));
            $entity->setDocument($arr['document']);
            $manager->persist($entity);
            $this->addReference('curriculumInventoryExports' . $arr['report'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            'Tests\AppBundle\Fixture\LoadUserData',
            'Tests\AppBundle\Fixture\LoadProgramYearData',
            'Tests\AppBundle\Fixture\LoadCurriculumInventoryReportData',
        );
    }
}
