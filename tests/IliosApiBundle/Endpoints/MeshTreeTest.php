<?php

namespace Tests\IliosApiBundle\Endpoints;

use Tests\IliosApiBundle\AbstractEndpointTest;

/**
 * MeshTree API endpoint Test.
 * @package Tests\IliosApiBundle\Endpoints
 * @group api_1
 */
class MeshTreeTest extends AbstractEndpointTest
{
    protected $testName =  'meshtree';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'Tests\CoreBundle\Fixture\LoadMeshTreeData',
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest()
    {
        return [
            'treeNumber' => ['treeNumber', $this->getFaker()->text],
            'descriptor' => ['descriptor', $this->getFaker()->text],
        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnliesToTest()
    {
        return [
            'id' => ['id', 1, 99],
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest()
    {
        return [
            'id' => [[0], ['id' => 1]],
            'treeNumber' => [[0], ['treeNumber' => 'test']],
            'descriptor' => [[0], ['descriptor' => 'test']],
        ];
    }

}