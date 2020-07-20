<?php

declare(strict_types=1);

namespace App\Tests\DataLoader;

use App\Entity\DTO\MeshDescriptorDTO;

class MeshDescriptorData extends AbstractDataLoader
{
    protected function getData()
    {
        $arr = [];
        $arr[] = [
            'id' => 'abc1',
            'name' => 'desc1' . $this->faker->text,
            'annotation' => 'annotation1',
            'courses' => ["1"],
            'sessionLearningMaterials' => ['1'],
            'courseLearningMaterials' => ['1', '3'],
            'sessions' => ['1'],
            'concepts' => ['1', '2'],
            'qualifiers' => ['1', '2'],
            'trees' => ['1', '2'],
            'previousIndexing' => '1',
            'deleted' => false,
            'sessionObjectives' => [],
            'courseObjectives' => [],
            'programYearObjectives' => []
        ];
        $arr[] = [
            'id' => 'abc2',
            'name' => 'desc2' . $this->faker->text,
            'annotation' => 'annotation2' . $this->faker->text,
            'courses' => [],
            'sessionLearningMaterials' => ["2", "3", "4", "5", "6", "7", "8"],
            'courseLearningMaterials' => [],
            'sessions' => ["3"],
            'concepts' => [],
            'qualifiers' => [],
            'trees' => [],
            'previousIndexing' => '2',
            'deleted' => false,
            'sessionObjectives' => [],
            'courseObjectives' => [],
            'programYearObjectives' => []
        ];
        $arr[] = [
            'id' => 'abc3',
            'name' => 'desc3',
            'annotation' => 'annotation3' . $this->faker->text,
            'courses' => [],
            'sessionLearningMaterials' => [],
            'courseLearningMaterials' => [],
            'sessions' => [],
            'concepts' => [],
            'qualifiers' => [],
            'trees' => [],
            'deleted' => false,
            'sessionObjectives' => [],
            'courseObjectives' => [],
            'programYearObjectives' => []
        ];

        return $arr;
    }

    public function create()
    {
        return [
            'id' => 'abc4',
            'name' => $this->faker->text(20),
            'annotation' => $this->faker->text,
            'courses' => ['1'],
            'sessionLearningMaterials' => ['1'],
            'courseLearningMaterials' => ['1'],
            'sessions' => ['1'],
            'concepts' => ['1'],
            'qualifiers' => ['1'],
            'trees' => [],
            'deleted' => false,
            'sessionObjectives' => [],
            'courseObjectives' => [],
            'programYearObjectives' => [],
        ];
    }

    public function createInvalid()
    {
        return [
            'id' => 'bad'
        ];
    }

    /**
     * Mesh descriptor IDs are strings so we have to convert them
     * @inheritdoc
     */
    public function createMany($count)
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $arr = $this->create();
            $arr['id'] = $arr['id'] . $i;
            $data[] = $arr;
        }

        return $data;
    }

    public function getDtoClass(): string
    {
        return MeshDescriptorDTO::class;
    }
}
