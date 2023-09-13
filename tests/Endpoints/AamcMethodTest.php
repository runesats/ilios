<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\Fixture\LoadAamcMethodData;
use App\Tests\Fixture\LoadSessionTypeData;
use App\Tests\AbstractReadWriteEndpoint;

/**
 * AamcMethod API endpoint Test.
 * @group api_1
 */
class AamcMethodTest extends AbstractReadWriteEndpoint
{
    protected string $testName =  'aamcMethods';

    protected function getFixtures(): array
    {
        return [
            LoadAamcMethodData::class,
            LoadSessionTypeData::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest(): array
    {
        return [
            'description' => ['description', 'lorem ipsum'],
            'sessionTypes' => ['sessionTypes', [1]],
            'id' => ['id', 'NEW1', $skip = true],
            'active' => ['active', false],

        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest(): array
    {
        return [
            'id' => [[0], ['id' => 'AM001']],
            'ids' => [[0, 1], ['id' => ['AM001', 'AM002']]],
            'description' => [[1], ['description' => 'filterable description']],
            'sessionTypes' => [[0], ['sessionTypes' => [1]]],
            'active' => [[0], ['active' => true]],
            'notActive' => [[1], ['active' => false]],
        ];
    }
    public function graphQLFiltersToTest(): array
    {
        $filters = $this->filtersToTest();
        $filters['ids'] = [[0, 1], ['ids' => ['AM001', 'AM002']]];

        return $filters;
    }
}
