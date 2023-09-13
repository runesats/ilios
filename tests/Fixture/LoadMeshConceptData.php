<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Entity\MeshConcept;
use App\Entity\MeshDescriptor;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMeshConceptData extends AbstractFixture implements
    ORMFixtureInterface,
    ContainerAwareInterface,
    DependentFixtureInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $data = $this->container
            ->get('App\Tests\DataLoader\MeshConceptData')
            ->getAll();
        foreach ($data as $arr) {
            $entity = new MeshConcept();
            $entity->setId($arr['id']);
            $entity->setName($arr['name']);
            $entity->setPreferred($arr['preferred']);
            $entity->setScopeNote($arr['scopeNote']);
            $entity->setCasn1Name($arr['casn1Name']);
            $entity->setRegistryNumber($arr['registryNumber']);
            foreach ($arr['descriptors'] as $id) {
                $entity->addDescriptor($this->getReference('meshDescriptors' . $id, MeshDescriptor::class));
            }
            $this->addReference('meshConcepts' . $arr['id'], $entity);
            $manager->persist($entity);
            $manager->flush();
        }
    }

    public function getDependencies()
    {
        return [
            'App\Tests\Fixture\LoadMeshDescriptorData',
        ];
    }
}
