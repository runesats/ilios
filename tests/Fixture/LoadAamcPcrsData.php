<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\AamcPcrs;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAamcPcrsData extends AbstractFixture implements
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
            ->get('App\Tests\DataLoader\AamcPcrsData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new AamcPcrs();
            $entity->setId($arr['id']);
            $entity->setDescription($arr['description']);
            $manager->persist($entity);
            $this->addReference('aamcPcrs' . $arr['id'], $entity);
            $manager->flush();
        }
    }
}
