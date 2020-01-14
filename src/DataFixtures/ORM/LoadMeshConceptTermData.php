<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Manager\MeshDescriptorManager;
use App\Service\DataimportFileLocator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class LoadMeshConceptTermData
 */
class LoadMeshConceptTermData extends AbstractMeshFixture implements DependentFixtureInterface
{
    public function __construct(
        MeshDescriptorManager $meshDescriptorManager,
        DataimportFileLocator $dataimportFileLocator
    ) {
        parent::__construct(
            $meshDescriptorManager,
            $dataimportFileLocator,
            'mesh_concept_x_term.csv',
            'MeshConceptTerm'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
          'App\DataFixtures\ORM\LoadMeshConceptData',
          'App\DataFixtures\ORM\LoadMeshTermData',
        ];
    }
}
