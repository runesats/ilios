<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\PendingUserUpdate;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPendingUserUpdateData extends AbstractFixture implements
    ORMFixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('App\Tests\DataLoader\PendingUserUpdateData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new PendingUserUpdate();
            $entity->setId($arr['id']);
            $entity->setType($arr['type']);
            $entity->setProperty($arr['property']);
            $entity->setValue($arr['value']);
            $entity->setUser($this->getReference('users' . $arr['user']));

            $manager->persist($entity);
            $this->addReference('pendingUserUpdates' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            'App\Tests\Fixture\LoadUserData'
        ];
    }
}
