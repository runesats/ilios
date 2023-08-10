<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\LearningMaterialStatus;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadLearningMaterialStatusData extends AbstractFixture implements
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
            ->get('App\Tests\DataLoader\LearningMaterialStatusData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new LearningMaterialStatus();
            $entity->setId($arr['id']);
            $entity->setTitle($arr['title']);
            $manager->persist($entity);
            $this->addReference('learningMaterialStatus' . $arr['id'], $entity);
            $manager->flush();
        }
    }
}
