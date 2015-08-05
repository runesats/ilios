<?php

namespace Ilios\CoreBundle\Tests\DataLoader;

class SessionData extends AbstractDataLoader
{
    protected function getData()
    {
        $arr = array();

        $arr[] = array(
            'id' => 1,
            'title' => $this->faker->text(10),
            'attireRequired' => false,
            'equipmentRequired' => false,
            'supplemental' => false,
            'deleted' => false,
            'publishedAsTbd' => false,
            'sessionType' => '1',
            'course' => '1',
            'sessionDescription' => '1',
            'disciplines' => ['1', '2'],
            'objectives' => ['1', '2'],
            'meshDescriptors' => [],
            'publishEvent' => '1',
            'sessionLearningMaterials' => ['1'],
            'instructionHours' => [],
            'offerings' => ['1', '2']
        );

        $arr[] = array(
            'id' => 2,
            'title' => $this->faker->text(10),
            'attireRequired' => false,
            'equipmentRequired' => false,
            'supplemental' => false,
            'deleted' => false,
            'publishedAsTbd' => false,
            'sessionType' => '1',
            'course' => '1',
            'sessionDescription' => '2',
            'disciplines' => [],
            'objectives' => [],
            'meshDescriptors' => [],
            'sessionLearningMaterials' => [],
            'instructionHours' => [],
            'offerings' => ['3', '4', '5']
        );

        $arr[] = array(
            'id' => 3,
            'title' => $this->faker->text(10),
            'attireRequired' => false,
            'equipmentRequired' => false,
            'supplemental' => false,
            'deleted' => false,
            'publishedAsTbd' => false,
            'course' => '2',
            'disciplines' => [],
            'objectives' => [],
            'meshDescriptors' => [],
            'sessionLearningMaterials' => [],
            'instructionHours' => [],
            'offerings' => ['6', '7']
        );
        
        for ($i = 4; $i <= 7; $i++) {
            $arr[] = array(
                'id' => $i,
                'title' => $this->faker->text(10),
                'attireRequired' => false,
                'equipmentRequired' => false,
                'supplemental' => false,
                'deleted' => false,
                'publishedAsTbd' => false,
                'course' => '2',
                'ilmSession' => $i - 3,
                'disciplines' => [],
                'objectives' => [],
                'meshDescriptors' => [],
                'sessionLearningMaterials' => [],
                'instructionHours' => [],
                'offerings' => []
            );
        }


        return $arr;
    }

    public function create()
    {
        return array(
            'id' => 8,
            'title' => $this->faker->text(10),
            'attireRequired' => false,
            'equipmentRequired' => false,
            'supplemental' => false,
            'deleted' => false,
            'publishedAsTbd' => false,
            'sessionType' => '1',
            'course' => '1',
            'disciplines' => ['1', '2'],
            'objectives' => ['1', '2'],
            'meshDescriptors' => [],
            'publishEvent' => '1',
            'sessionLearningMaterials' => ["1"],
            'instructionHours' => [],
            'offerings' => ['1']
        );
    }

    public function createInvalid()
    {
        return [];
    }
}
