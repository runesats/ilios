<?php

namespace Ilios\CoreBundle\Tests\DataFixtures\ORM;

use Ilios\CoreBundle\Entity\Manager\ManagerInterface;

use Liip\FunctionalTestBundle\Test\WebTestCase;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for data loader tests.
 *
 * Class AbstractDataFixtureTest
 * @package Ilios\CoreBundle\Tests\DataFixtures\ORM
 */
abstract class AbstractDataFixtureTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->container = static::createClient()->getContainer();
        $this->loadEntityManager($this->getEntityManagerServiceKey());
    }

    /**
     * @param string $serviceKey
     */
    protected function loadEntityManager($serviceKey)
    {
        $this->em = $this->container->get($serviceKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            'Ilios\CoreBundle\DataFixtures\ORM\LoadSchoolData',
        ];
    }

    /**
     * Executes the test.
     * Call this from loadTest() implementations in child classes.
     *
     * @param string $fileName name of the data file to load and import.
     *
     * @see AbstractDataFixtureTest::loadTest()
     */
    protected function runTestLoad($fileName)
    {
        $this->loadFixtures($this->getFixtures());

        $dataFile = fopen($this->container->get('ilioscore.dataimport_filelocator')->getDataFilePath($fileName), 'r');

        $first = true;
        while (($data = fgetcsv($dataFile)) !== false) {
            // step over the first row
            // since it contains the field names
            if ($first) {
                $first = false;
                continue;
            }
            $entity = $this->getEntity($data);
            $this->assertDataEquals($data, $entity);
        }

        fclose($dataFile);
    }

    /**
     * Returns the key of the entity manager service that needs to be loaded for this test.
     *
     * @return string
     */
    abstract public function getEntityManagerServiceKey();

    /**
     * Implement this method to provide coverage for a data loader's load() method.
     */
    abstract public function testLoad();

    /**
     * Asserts data equality of a given array and entity.
     *
     * @param array $data
     * @param mixed $entity
     */
    abstract protected function assertDataEquals(array $data, $entity);

    /**
     * Retrieves an the corresponding entity for a given data array.
     *
     * @param array $data
     * @return mixed the entity
     */
    abstract protected function getEntity(array $data);
}
