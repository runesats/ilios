<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\DataLoader\CurriculumInventoryReportData;
use App\Tests\ReadWriteEndpointTest;

/**
 * CurriculumInventorySequence API endpoint Test.
 * @group api_1
 */
class CurriculumInventorySequenceTest extends ReadWriteEndpointTest
{
    protected $testName =  'curriculumInventorySequences';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadCurriculumInventorySequenceData',
            'App\Tests\Fixture\LoadCurriculumInventoryReportData'
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest()
    {
        return [
            'description' => ['description', $this->getFaker()->text],
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
            'report' => [[1], ['report' => 2]],
            'description' => [[1], ['description' => 'second description']],
        ];
    }

    /**
     * We need to create additional reports to go with each Sequence
     * however when new reports are created a sequence is automatically created
     * for them.  So we need to delete each of the new fresh sequences so we can create
     * new ones of our own and link them to the report.
     */
    protected function createMany(int $count): array
    {
        $reportDataLoader = $this->getContainer()->get(CurriculumInventoryReportData::class);
        $reports = $reportDataLoader->createMany($count);
        $savedReports = $this->postMany('curriculuminventoryreports', 'curriculumInventoryReports', $reports);


        $dataLoader = $this->getDataLoader();
        $data = [];

        foreach ($savedReports as $i => $report) {
            $sequenceId = $report['sequence'];
            $this->deleteOne('curriculuminventorysequences', $sequenceId);
            $arr = $dataLoader->create();
            $arr['id'] += ($i + $count);
            $arr['report'] = $report['id'];

            $data[] = $arr;
        }

        return $data;
    }

    public function testPostMany()
    {
        $data = $this->createMany(4);
        $this->postManyTest($data);
    }

    public function testPostManyJsonApi()
    {
        $data = $this->createMany(4);
        $jsonApiData = $this->getDataLoader()->createBulkJsonApi($data);
        $this->postManyJsonApiTest($jsonApiData, $data);
    }
}
