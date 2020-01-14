<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\DataLoader\DepartmentData;
use App\Tests\ReadWriteEndpointTest;

/**
 * ProgramYearSteward API endpoint Test.
 * @group api_2
 */
class ProgramYearStewardTest extends ReadWriteEndpointTest
{
    protected $testName =  'programYearStewards';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadProgramYearStewardData',
            'App\Tests\Fixture\LoadDepartmentData',
            'App\Tests\Fixture\LoadProgramYearData',
            'App\Tests\Fixture\LoadSchoolData'
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest()
    {
        return [
            'department' => ['department', 3],
            'programYear' => ['programYear', 2],
            'school' => ['school', 2],
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
            'department' => [[1], ['department' => 2]],
            'programYear' => [[0, 1], ['programYear' => 1]],
            'school' => [[0, 1], ['school' => 1]],
        ];
    }

    /**
     * Creating many runs into UNIQUE constraints quick
     * so instead build a bunch of new departments to use
     */
    public function testPostMany()
    {
        $departmentDataLoader = $this->getContainer()->get(DepartmentData::class);
        $departments = $departmentDataLoader->createMany(51);
        $savedDepartments = $this->postMany('departments', 'departments', $departments);

        $dataLoader = $this->getDataLoader();

        $data = [];
        foreach ($savedDepartments as $i => $department) {
            $arr = $dataLoader->create();
            $arr['id'] += $i;
            $arr['department'] = (string) $department['id'];

            $data[] = $arr;
        }


        $this->postManyTest($data);
    }

    /**
     * Override this so we don't change any values.  Changing something
     * like department or school causes key conflicts and there isn't really
     * anything non-unique to change here.
     */
    public function testPutForAllData()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        foreach ($all as $data) {
            $this->putTest($data, $data, $data['id']);
        }
    }

    /**
     * Tests creation of a new steward at the school level, without department.
     */
    public function testPostStewardWithoutDepartment()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->create();
        $this->assertNotEmpty($data['department']);
        unset($data['department']);
        $postData = $data;
        $this->postTest($data, $postData);
    }
}
