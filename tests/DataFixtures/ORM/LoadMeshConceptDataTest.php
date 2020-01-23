<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures\ORM;

use App\Entity\MeshConceptInterface;

/**
 * Class LoadMeshConceptDataTest
 */
class LoadMeshConceptDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'App\Entity\Manager\MeshConceptManager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
          'App\DataFixtures\ORM\LoadMeshConceptData',
        ];
    }

    /**
     * @covers \App\DataFixtures\ORM\LoadMeshConceptData::load
     * @group mesh_data_import
     */
    public function testLoad()
    {
        $this->runTestLoad('mesh_concept.csv', 10);
    }

    /**
     * @param array $data
     * @param MeshConceptInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `mesh_concept_uid`,`name`,`preferred`,`scope_note`,`casn_1_name`, `registry_number`
        $this->assertEquals($data[0], $entity->getId());
        $this->assertEquals($data[1], $entity->getName());
        $this->assertEquals((bool) $data[2], $entity->getPreferred());
        $this->assertEquals($data[3], $entity->getScopeNote());
        $this->assertEquals($data[4], $entity->getCasn1Name());
        $this->assertEquals($data[5], $entity->getRegistryNumber());
    }
}
