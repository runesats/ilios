<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\CourseClerkshipType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCourseClerkshipTypeData extends AbstractFixture implements
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
            ->get('App\Tests\DataLoader\CourseClerkshipTypeData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new CourseClerkshipType();
            $entity->setId($arr['id']);
            $entity->setTitle($arr['title']);
            $manager->persist($entity);
            $this->addReference('courseClerkshipTypes' . $arr['id'], $entity);
        }

        $manager->flush();
    }
}
