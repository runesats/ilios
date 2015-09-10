<?php

namespace Ilios\CoreBundle\Tests\DataFixtures\ORM;

use Ilios\CoreBundle\Entity\Manager\MeshDescriptorManagerInterface;
use Ilios\CoreBundle\Entity\MeshDescriptorInterface;

/**
 * Class LoadMeshDescriptorDataTest
 * @package Ilios\CoreBundle\Tests\DataFixtures\ORM
 */
class LoadMeshDescriptorDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'ilioscore.meshdescriptor.manager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
          'Ilios\CoreBundle\DataFixtures\ORM\LoadMeshDescriptorData',
        ];
    }

    /**
     * @covers Ilios\CoreBundle\DataFixtures\ORM\LoadMeshDescriptorData::load
     */
    public function testLoad()
    {
        $this->runTestLoad('mesh_descriptor.csv', 10);
    }

    /**
     * @param array $data
     * @param MeshDescriptorInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `mesh_descriptor_uid`,`name`,`annotation`,`created_at`,`updated_at`
        $this->assertEquals($data[0], $entity->getId());
        $this->assertEquals($data[1], $entity->getName());
        $this->assertEquals($data[2], $entity->getAnnotation());
        $this->assertEquals(new \DateTime($data[3], new \DateTimeZone('UTC')), $entity->getCreatedAt());
        $this->assertEquals(new \DateTime($data[4], new \DateTimeZone('UTC')), $entity->getUpdatedAt());
    }

    /**
     * @param array $data
     * @return MeshDescriptorInterface
     * @override
     */
    protected function getEntity(array $data)
    {
        /**
         * @var MeshDescriptorManagerInterface $em
         */
        $em = $this->em;
        return $em->findMeshDescriptorBy(['id' => $data[0]]);
    }
}
