<?php

namespace Ilios\CoreBundle\Tests\Fixture;

use Ilios\CoreBundle\Entity\SessionLearningMaterial;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadSessionLearningMaterialData extends AbstractFixture implements
    FixtureInterface,
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
            ->get('ilioscore.dataloader.sessionLearningMaterial')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new SessionLearningMaterial();
            $entity->setId($arr['id']);
            $entity->setRequired($arr['required']);
            $entity->setPublicNotes($arr['publicNotes']);
            $entity->setNotes($arr['notes']);
            $entity->setSession($this->getReference('sessions' . $arr['session']));
            if ($arr['learningMaterial']) {
                $entity->setLearningMaterial($this->getReference('learningMaterials' . $arr['learningMaterial']));
            }
            
            $manager->persist($entity);
            $this->addReference('sessionLearningMaterials' . $arr['id'], $entity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionData',
            'Ilios\CoreBundle\Tests\Fixture\LoadLearningMaterialData',
        );
    }
}
