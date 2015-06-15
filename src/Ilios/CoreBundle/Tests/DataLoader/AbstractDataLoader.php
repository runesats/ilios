<?php

namespace Ilios\CoreBundle\Tests\DataLoader;

use Faker\Factory as FakerFactory;

/**
 * Abstract utilities for loading data
 *
 * @package Ilios\CoreBundle\Tests\DataLoader
 *
 */
abstract class AbstractDataLoader implements DataLoaderInterface
{
    protected $data;

    protected $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
        $this->faker->seed(1234);
    }


    /**
     * Create test data
     * @return array
     */
    abstract protected function getData();

    /**
     * [setup description]
     * @return [type] [description]
     */
    protected function setup()
    {
        if (!empty($this->data)) {
            return;
        }

        $this->data = $this->getData();
    }


    public function getOne()
    {
        $this->setUp();
        return array_values($this->data)[0];
    }

    public function getAll()
    {
        $this->setUp();
        return $this->data;
    }

    abstract public function create();

    abstract public function createInvalid();
}
