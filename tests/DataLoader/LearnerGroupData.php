<?php

declare(strict_types=1);

namespace App\Tests\DataLoader;

use App\Entity\DTO\LearnerGroupDTO;

class LearnerGroupData extends AbstractDataLoader
{
    protected function getData()
    {
        $arr = [];

        $arr[] = [
            'id' => 1,
            'title' => $this->faker->text(25),
            'location' => $this->faker->text(25),
            'cohort' => '1',
            'children' => ['4'],
            'ilmSessions' => ['1'],
            'offerings' => ['1'],
            'instructorGroups' => ['1'],
            'users' => ['2', '5'],
            'instructors' => ['1'],
            'descendants' => [],
        ];

        $arr[] = [
            'id' => 2,
            'title' => $this->faker->text(25),
            'cohort' => '2',
            'children' => [],
            'ilmSessions' => [],
            'offerings' => ['2'],
            'instructorGroups' => [],
            'users' => ['2'],
            'instructors' => [],
            'descendants' => [],
        ];

        $arr[] = [
            'id' => 3,
            'title' => 'third learner group',
            'cohort' => '1',
            'children' => [],
            'ilmSessions' => ['1'],
            'offerings' => [],
            'instructorGroups' => [],
            'users' => ['2'],
            'instructors' => ['1'],
            'descendants' => ['4'],
        ];


        $arr[] = [
            'id' => 4,
            'title' => $this->faker->text(25),
            'location' => 'fourth location',
            'cohort' => '1',
            'children' => [],
            'parent' => '1',
            'ancestor' => '3',
            'ilmSessions' => [],
            'offerings' => [],
            'instructorGroups' => [],
            'users' => [],
            'instructors' => [],
            'descendants' => [],
        ];


        $arr[] = [
            'id' => 5,
            'title' => $this->faker->text(25),
            'cohort' => '1',
            'children' => [],
            'ilmSessions' => [],
            'offerings' => ['1', '2'],
            'instructorGroups' => [],
            'users' => ['5'],
            'instructors' => [],
            'descendants' => [],
        ];


        return $arr;
    }

    public function create()
    {
        return [
            'id' => 6,
            'title' => $this->faker->text(25),
            'cohort' => "1",
            'ancestor' => '2',
            'children' => [],
            'ilmSessions' => ['1'],
            'offerings' => ['1'],
            'instructorGroups' => [],
            'users' => [],
            'instructors' => [],
            'descendants' => [],
        ];
    }

    public function createInvalid()
    {
        return [];
    }

    public function createJsonApi(array $arr): object
    {
        $item = $this->buildJsonApiObject($arr, LearnerGroupDTO::class);
        return json_decode(json_encode(['data' => $item]), false);
    }
}
