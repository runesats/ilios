<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Tests\DataLoader\ApplicationConfigData;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\ApplicationConfig;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadApplicationConfigData extends AbstractFixture implements
    ORMFixtureInterface,
    ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get(ApplicationConfigData::class)
            ->getAll();
        foreach ($data as $arr) {
            $entity = new ApplicationConfig();
            $entity->setId($arr['id']);
            $entity->setName($arr['name']);
            $entity->setValue($arr['value']);
            $manager->persist($entity);
            $this->addReference('applicationConfigs' . $arr['id'], $entity);
            $manager->flush();
        }
    }
}
