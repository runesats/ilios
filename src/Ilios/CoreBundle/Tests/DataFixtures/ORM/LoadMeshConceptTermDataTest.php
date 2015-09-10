<?php

namespace Ilios\CoreBundle\Tests\DataFixtures\ORM;

use Ilios\CoreBundle\Entity\Manager\MeshConceptManagerInterface;
use Ilios\CoreBundle\Entity\MeshConceptInterface;
use Ilios\CoreBundle\Entity\MeshTermInterface;

/**
 * Class LoadMeshConceptTermDataTest
 * @package Ilios\CoreBundle\Tests\DataFixtures\ORM
 */
class LoadMeshConceptTermDataTest extends AbstractDataFixtureTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityManagerServiceKey()
    {
        return 'ilioscore.meshconcept.manager';
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
          'Ilios\CoreBundle\DataFixtures\ORM\LoadMeshConceptTermData',
        ];
    }

    /**
     * @covers Ilios\CoreBundle\DataFixtures\ORM\LoadMeshConceptTermData::load
     */
    public function testLoad()
    {
        $this->runTestLoad('mesh_concept_x_term.csv', 10);
    }

    /**
     * @param array $data
     * @param MeshConceptInterface $entity
     */
    protected function assertDataEquals(array $data, $entity)
    {
        // `mesh_concept_uid`,`mesh_term_id`
        $this->assertEquals($data[0], $entity->getId());
        // find the term
        $termId = $data[1];
        $term = $entity->getTerms()->filter(function (MeshTermInterface $term) use ($termId) {
            return $term->getId() === $termId;
        })->first();
        $this->assertNotEmpty($term);
    }

    /**
     * @param array $data
     * @return MeshConceptInterface
     * @override
     */
    protected function getEntity(array $data)
    {
        /**
         * @var MeshConceptManagerInterface $em
         */
        $em = $this->em;
        return $em->findMeshConceptBy(['id' => $data[0]]);
    }
}
