<?php

declare(strict_types=1);

namespace App\Service\DataLoader;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\DataFixtures\AbstractFixture as DataFixture;
use Doctrine\Persistence\ObjectManager;
use App\Service\DataimportFileLocator;
use App\Traits\IdentifiableEntityInterface;

/**
 * A generic data-loader base-class for importing entities from data files.
 *
 * Class AbstractFixture
 *
 * @link http://docs.doctrine-project.org/en/latest/reference/batch-processing.html#bulk-inserts
 */
abstract class AbstractFixture extends DataFixture implements ORMFixtureInterface
{
    /**
     * number of insert statements per batch.
     */
    protected const BATCH_SIZE = 200;

    public function __construct(
        private DataimportFileLocator $dataimportFileLocator,
        protected string $key,
        protected bool $storeReference = true
    ) {
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Connection $connection */
        $connection = $manager->getConnection();

        // disable the SQL logger
        // @link http://stackoverflow.com/a/30924545
        $connection->getConfiguration()->setSQLLogger(null);

        $fileName = $this->getKey() . '.csv';
        $path = $this->dataimportFileLocator->getDataFilePath($fileName);

        // honor the given entity identifiers.
        // @link http://www.ens.ro/2012/07/03/symfony2-doctrine-force-entity-id-on-persist/
        $manager
          ->getClassMetadata($this->createEntity()::class)
          ->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $i = 0;

        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $i++;
                // step over the first row
                // since it contains the field names
                if (1 === $i) {
                    continue;
                }

                $entity = $this->populateEntity($this->createEntity(), $data);
                $manager->persist($entity);

                if (($i % self::BATCH_SIZE) === 0) {
                    $manager->flush();
                    $manager->clear();
                }

                if ($this->storeReference) {
                    $this->addReference(
                        $this->getKey() . $entity->getId(),
                        $entity
                    );
                }
            }

            $manager->flush();
            $manager->clear();

            fclose($handle);
        }

        // Force PHP's GC
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    /**
     * Instantiates and returns the primary target entity of this fixture.
     *
     * @return mixed
     */
    abstract protected function createEntity();

    /**
     * Populates a given entity with the data contained in a given array,
     * then returns the populated entity.
     * Note that data persistence is not in scope for this method.
     *
     * @param mixed $entity
     * @return IdentifiableEntityInterface
     */
    abstract protected function populateEntity($entity, array $data);
}
