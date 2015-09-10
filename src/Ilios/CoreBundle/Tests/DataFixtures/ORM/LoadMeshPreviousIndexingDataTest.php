<?php

namespace Ilios\CoreBundle\Tests\DataFixtures\ORM;

use Ilios\CoreBundle\Entity\Manager\MeshPreviousIndexingManagerInterface;
use Ilios\CoreBundle\Entity\MeshPreviousIndexingInterface;

/**
 * Class LoadMeshPreviousIndexingDataTest
 * @package Ilios\CoreBundle\Tests\DataFixtures\ORM
 */
class LoadMeshPreviousIndexingDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'ilioscore.meshpreviousindexing.manager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
          'Ilios\CoreBundle\DataFixtures\ORM\LoadMeshPreviousIndexingData',
        ];
    }

    /**
     * @covers Ilios\CoreBundle\DataFixtures\ORM\LoadMeshPreviousIndexingData::load
     */
    public function testLoad()
    {
        $this->runTestLoad('mesh_previous_indexing.csv', 10);
    }

    /**
     * @param array $data
     * @param MeshPreviousIndexingInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `mesh_descriptor_uid`,`previous_indexing`,`mesh_previous_indexing_id`
        $this->assertEquals($data[0], $entity->getDescriptor()->getId());
        $this->assertEquals($data[1], $entity->getPreviousIndexing());
        $this->assertEquals($data[2], $entity->getId());
    }

    /**
     * @param array $data
     * @return MeshPreviousIndexingInterface
     * @override
     */
    protected function getEntity(array $data)
    {
        /**
         * @var MeshPreviousIndexingManagerInterface $em
         */
        $em = $this->em;
        return $em->findMeshPreviousIndexingBy(['id' => $data[2]]);
    }
}
