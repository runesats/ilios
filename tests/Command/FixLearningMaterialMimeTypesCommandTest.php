<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\FixLearningMaterialMimeTypesCommand;
use App\Entity\LearningMaterialInterface;
use App\Repository\LearningMaterialRepository;
use App\Service\TemporaryFileSystem;
use ErrorException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;
use App\Service\IliosFileSystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FixLearningMaterialMimeTypesCommandTest
 * @package App\Tests\Command
 * @group cli
 */
class FixLearningMaterialMimeTypesCommandTest extends KernelTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'ilios:fix-mime-types';

    protected $iliosFileSystem;
    protected $temporaryFileSystem;
    protected $learningMaterialRepository;
    protected $commandTester;

    public function setUp(): void
    {
        parent::setUp();
        $this->iliosFileSystem = m::mock(IliosFileSystem::class);
        $this->temporaryFileSystem = m::mock(TemporaryFileSystem::class);
        $this->learningMaterialRepository = m::mock(LearningMaterialRepository::class);

        $command = new FixLearningMaterialMimeTypesCommand(
            $this->iliosFileSystem,
            $this->temporaryFileSystem,
            $this->learningMaterialRepository
        );
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add($command);
        $commandInApp = $application->find(self::COMMAND_NAME);
        $this->commandTester = new CommandTester($commandInApp);
    }

    /**
     * Remove all mock objects
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->iliosFileSystem);
        unset($this->learningMaterialRepository);
        unset($this->commandTester);
    }

    public function testFixCitationType()
    {
        $this->learningMaterialRepository->shouldReceive('getTotalLearningMaterialCount')
            ->once()->andReturn(1);
        $mockLm = m::mock(LearningMaterialInterface::class);
        $mockLm->shouldReceive('getRelativePath')->once()->andReturn(null);
        $mockLm->shouldReceive('getCitation')->once()->andReturn('MST3k The Return S1 E6');
        $mockLm->shouldReceive('getMimetype')->once()->andReturn('link');
        $mockLm->shouldReceive('setMimetype')->with('citation')->once();
        $this->learningMaterialRepository->shouldReceive('findBy')->with([], ['id' => 'desc'], 50, 0)
            ->once()->andReturn([$mockLm]);
        $this->learningMaterialRepository->shouldReceive('update')->with($mockLm, false);
        $this->learningMaterialRepository->shouldReceive('flushAndClear')->once();

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute([
            'command' => self::COMMAND_NAME
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertMatchesRegularExpression(
            '/Ready to fix 1 learning materials. Shall we continue?/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/Updated mimetypes for 1 learning materials successfully!/',
            $output
        );
    }

    public function testFixBlankCitationType()
    {
        $this->learningMaterialRepository->shouldReceive('getTotalLearningMaterialCount')
            ->once()->andReturn(1);
        $mockLm = m::mock(LearningMaterialInterface::class);
        $mockLm->shouldReceive('getRelativePath')->once()->andReturn(null);
        $mockLm->shouldReceive('getCitation')->once()->andReturn('');
        $mockLm->shouldReceive('getMimetype')->once()->andReturn('link');
        $mockLm->shouldReceive('setMimetype')->with('citation')->once();
        $this->learningMaterialRepository->shouldReceive('findBy')->with([], ['id' => 'desc'], 50, 0)
            ->once()->andReturn([$mockLm]);
        $this->learningMaterialRepository->shouldReceive('update')->with($mockLm, false);
        $this->learningMaterialRepository->shouldReceive('flushAndClear')->once();

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute([
            'command' => self::COMMAND_NAME
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertMatchesRegularExpression(
            '/Ready to fix 1 learning materials. Shall we continue?/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/Updated mimetypes for 1 learning materials successfully!/',
            $output
        );
    }

    public function testFixLinkType()
    {
        $this->learningMaterialRepository->shouldReceive('getTotalLearningMaterialCount')
            ->once()->andReturn(1);
        $mockLm = m::mock(LearningMaterialInterface::class);
        $mockLm->shouldReceive('getRelativePath')->once()->andReturn(null);
        $mockLm->shouldReceive('getCitation')->once()->andReturn(null);
        $mockLm->shouldReceive('getLink')->once()->andReturn('https://example.com');
        $mockLm->shouldReceive('getMimetype')->once()->andReturn('citation');
        $mockLm->shouldReceive('setMimetype')->with('link')->once();
        $this->learningMaterialRepository->shouldReceive('findBy')->with([], ['id' => 'desc'], 50, 0)
            ->once()->andReturn([$mockLm]);
        $this->learningMaterialRepository->shouldReceive('update')->with($mockLm, false);
        $this->learningMaterialRepository->shouldReceive('flushAndClear')->once();

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute([
            'command' => self::COMMAND_NAME
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertMatchesRegularExpression(
            '/Ready to fix 1 learning materials. Shall we continue?/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/Updated mimetypes for 1 learning materials successfully!/',
            $output
        );
    }

    public function testFixBlankLinkType()
    {
        $this->learningMaterialRepository->shouldReceive('getTotalLearningMaterialCount')
            ->once()->andReturn(1);
        $mockLm = m::mock(LearningMaterialInterface::class);
        $mockLm->shouldReceive('getRelativePath')->once()->andReturn(null);
        $mockLm->shouldReceive('getCitation')->once()->andReturn(null);
        $mockLm->shouldReceive('getLink')->once()->andReturn('');
        $mockLm->shouldReceive('getMimetype')->once()->andReturn('citation');
        $mockLm->shouldReceive('setMimetype')->with('link')->once();
        $this->learningMaterialRepository->shouldReceive('findBy')->with([], ['id' => 'desc'], 50, 0)
            ->once()->andReturn([$mockLm]);
        $this->learningMaterialRepository->shouldReceive('update')->with($mockLm, false);
        $this->learningMaterialRepository->shouldReceive('flushAndClear')->once();

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute([
            'command' => self::COMMAND_NAME
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertMatchesRegularExpression(
            '/Ready to fix 1 learning materials. Shall we continue?/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/Updated mimetypes for 1 learning materials successfully!/',
            $output
        );
    }

    public function testFixFileType()
    {
        $this->learningMaterialRepository->shouldReceive('getTotalLearningMaterialCount')
            ->once()->andReturn(1);
        $mockLm = m::mock(LearningMaterialInterface::class);
        $mockLm->shouldReceive('getRelativePath')->once()->andReturn('/tmp/somewhere');
        $mockLm->shouldReceive('getMimetype')->once()->andReturn('citation');
        $mockLm->shouldReceive('setMimetype')->with('pdf-type-file')->once();

        $mockFile = m::mock(File::class)->shouldReceive('getMimeType')->once()->andReturn('pdf-type-file')->mock();
        $this->iliosFileSystem->shouldReceive('getFileContents')
            ->once()->with('/tmp/somewhere')->andReturn('some contents');
        $this->temporaryFileSystem->shouldReceive('createFile')->once()->with('some contents')->andReturn($mockFile);

        $this->learningMaterialRepository->shouldReceive('findBy')->with([], ['id' => 'desc'], 50, 0)
            ->once()->andReturn([$mockLm]);
        $this->learningMaterialRepository->shouldReceive('update')->with($mockLm, false);
        $this->learningMaterialRepository->shouldReceive('flushAndClear')->once();

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute([
            'command' => self::COMMAND_NAME
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertMatchesRegularExpression(
            '/Ready to fix 1 learning materials. Shall we continue?/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/Updated mimetypes for 1 learning materials successfully!/',
            $output
        );
    }

    public function testFixFileWithUndetectableTypeType()
    {
        $this->learningMaterialRepository->shouldReceive('getTotalLearningMaterialCount')
            ->once()->andReturn(1);
        $mockLm = m::mock(LearningMaterialInterface::class);
        $mockLm->shouldReceive('getRelativePath')->once()->andReturn('/tmp/somewhere');
        $mockLm->shouldReceive('getMimetype')->once()->andReturn('citation');
        $mockLm->shouldReceive('setMimetype')->with('application/pdf')->once();
        $mockLm->shouldReceive('getFilename')->once()->andReturn('test.pdf');


        $mockFile = m::mock(File::class)->shouldReceive('getMimeType')
            ->once()->andThrow(new ErrorException())->mock();
        $this->iliosFileSystem->shouldReceive('getFileContents')
            ->once()->with('/tmp/somewhere')->andReturn('some contents');
        $this->temporaryFileSystem->shouldReceive('createFile')->once()->with('some contents')->andReturn($mockFile);

        $this->learningMaterialRepository->shouldReceive('findBy')->with([], ['id' => 'desc'], 50, 0)
            ->once()->andReturn([$mockLm]);
        $this->learningMaterialRepository->shouldReceive('update')->with($mockLm, false);
        $this->learningMaterialRepository->shouldReceive('flushAndClear')->once();

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute([
            'command' => self::COMMAND_NAME
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertMatchesRegularExpression(
            '/Ready to fix 1 learning materials. Shall we continue?/',
            $output
        );
        $this->assertMatchesRegularExpression(
            '/Updated mimetypes for 1 learning materials successfully!/',
            $output
        );
    }
}
