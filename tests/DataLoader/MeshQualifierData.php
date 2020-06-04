<?php

declare(strict_types=1);

namespace App\Tests\DataLoader;

use App\Entity\DTO\MeshQualifierDTO;

class MeshQualifierData extends AbstractDataLoader
{
    protected function getData()
    {
        $arr = [];
        $arr[] = [
            'id' => '1',
            'name' => 'qual' . $this->faker->text(5),
            'descriptors' => ['abc1']
        ];
        $arr[] = [
            'id' => '2',
            'name' => 'second qualifier',
            'descriptors' => ['abc1']
        ];

        return $arr;
    }

    public function create()
    {
        return [
            'id' => '3',
            'name' => $this->faker->text(5),
            'descriptors' => []
        ];
    }

    public function createInvalid()
    {
        return [];
    }

    /**
     * Mesh qualifier IDs are strings so we have to convert them
     * @inheritdoc
     */
    public function createMany($count)
    {
        $data = parent::createMany($count);

        return array_map(function (array $arr) {
            $arr['id'] = (string) $arr['id'];

            return $arr;
        }, $data);
    }

    public function getDtoClass(): string
    {
        return MeshQualifierDTO::class;
    }
}
