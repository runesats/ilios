<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ExportMeshUniverseCommand;
use App\Entity\Manager\MeshDescriptorManager;
use App\Service\CsvWriter;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExportMeshUniverseCommandTest extends KernelTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'ilios:export-mesh-universe';

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var MeshDescriptorManager
     */
    protected $manager;

    /**
     * @var CsvWriter
     */
    protected $writer;

    /**
     * @var string
     */
    protected $path;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->manager = m::mock(MeshDescriptorManager::class);
        $this->writer = m::mock(CsvWriter::class);
        $this->path = '/path/to/approot';

        $command = new ExportMeshUniverseCommand($this->manager, $this->writer, $this->path);

        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add($command);
        $commandInApp = $application->find(self::COMMAND_NAME);
        $this->commandTester = new CommandTester($commandInApp);
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->manager);
        unset($this->writer);
        unset($this->path);
        unset($this->commandTester);
    }

    /**
     * @covers ExportMeshUniverseCommand::execute
     */
    public function testExecute()
    {
        $meshConceptData = [
            [
                'M0000001',
                'Calcimycin',
                true,
                'An ionophorous, polyether antibiotic from Streptomyces chartreusensis.',
                '4-Benzoxazolecarboxylic acid, ...',
                '37H9VM9WZL',
            ]
        ];
        $meshConceptHeader = [
            'mesh_concept_uid',
            'name',
            'preferred',
            'scope_note',
            'casn_1_name',
            'registry_number',
        ];
        $this->manager->shouldReceive('exportMeshConcepts')->once()->andReturn($meshConceptData);
        $this->writer->shouldReceive('writeToFile')
            ->with($meshConceptHeader, $meshConceptData, $this->path . '/config/dataimport/mesh_concept.csv');

        $meshConceptTermData = [['M0000001', 1]];
        $meshConceptTermHeader = ['mesh_concept_uid', 'mesh_term_id'];
        $this->manager->shouldReceive('exportMeshConceptTerms')->once()->andReturn($meshConceptTermData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshConceptTermHeader,
                $meshConceptTermData,
                $this->path . '/config/dataimport/mesh_concept_x_term.csv'
            );

        $meshDescriptorData = [['D000001', 'Calcimycin', null, false]];
        $meshDescriptorHeader = ['mesh_descriptor_uid', 'name', 'annotation', 'deleted'];
        $this->manager->shouldReceive('exportMeshDescriptors')->once()->andReturn($meshDescriptorData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshDescriptorHeader,
                $meshDescriptorData,
                $this->path . '/config/dataimport/mesh_descriptor.csv'
            );

        $meshDescriptorConceptData = [['M0000001', 'D000001']];
        $meshDescriptorConceptHeader = ['mesh_concept_uid', 'mesh_descriptor_uid'];
        $this->manager->shouldReceive('exportMeshDescriptorConcepts')->once()->andReturn($meshDescriptorConceptData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshDescriptorConceptHeader,
                $meshDescriptorConceptData,
                $this->path . '/config/dataimport/mesh_descriptor_x_concept.csv'
            );

        $meshDescriptorQualifierData = [['D000001', 'Q000008']];
        $meshDescriptorQualifierHeader = ['mesh_descriptor_uid', 'mesh_qualifier_uid'];
        $this->manager
            ->shouldReceive('exportMeshDescriptorQualifiers')
            ->once()
            ->andReturn($meshDescriptorQualifierData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshDescriptorQualifierHeader,
                $meshDescriptorQualifierData,
                $this->path . '/config/dataimport/mesh_descriptor_x_qualifier.csv'
            );

        $meshPreviousIndexingData = [['D000001', 'Carboxylic Acids (1973-1974)', 1]];
        $meshPreviousIndexingHeader = ['mesh_descriptor_uid', 'previous_indexing', 'mesh_previous_indexing_id'];
        $this->manager->shouldReceive('exportMeshPreviousIndexings')->once()->andReturn($meshPreviousIndexingData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshPreviousIndexingHeader,
                $meshPreviousIndexingData,
                $this->path . '/config/dataimport/mesh_previous_indexing.csv'
            );

        $meshQualifierData = [['Q000000981', 'diagnostic imaging']];
        $meshQualifierHeader = ['mesh_qualifier_uid', 'name'];
        $this->manager->shouldReceive('exportMeshQualifiers')->once()->andReturn($meshQualifierData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshQualifierHeader,
                $meshQualifierData,
                $this->path . '/config/dataimport/mesh_qualifier.csv'
            );

        $meshTermData = [
            ['T000002', 'Calcimycin', 'NON', true, true, false, 1]
        ];
        $meshTermHeader = [
            'mesh_term_uid',
            'name',
            'lexical_tag',
            'concept_preferred',
            'record_preferred',
            'permuted',
            'mesh_term_id',
        ];
        $this->manager->shouldReceive('exportMeshTerms')->once()->andReturn($meshTermData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshTermHeader,
                $meshTermData,
                $this->path . '/config/dataimport/mesh_term.csv'
            );

        $meshTreeData = [['D03.633.100.221.173', 'D000001', 1]];
        $meshTreeHeader = ['tree_number', 'mesh_descriptor_uid', 'mesh_tree_id'];
        $this->manager->shouldReceive('exportMeshTrees')->once()->andReturn($meshTreeData);
        $this->writer->shouldReceive('writeToFile')
            ->with(
                $meshTreeHeader,
                $meshTreeData,
                $this->path . '/config/dataimport/mesh_tree.csv'
            );

        $this->commandTester->execute([]);
    }
}
