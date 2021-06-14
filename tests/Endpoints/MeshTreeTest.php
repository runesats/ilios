<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

/**
 * MeshTree API endpoint Test.
 * @group api_4
 */
class MeshTreeTest extends AbstractMeshTest
{
    protected string $testName =  'meshTrees';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadMeshTreeData',
        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest()
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
            'ids' => [[0, 1], ['id' => [1, 2]]],
            'treeNumber' => [[1], ['treeNumber' => 'tree2']],
            'descriptor' => [[0, 1], ['descriptor' => 'abc1']],
        ];
    }
}
