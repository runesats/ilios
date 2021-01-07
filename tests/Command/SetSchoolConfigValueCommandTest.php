<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SetSchoolConfigValueCommand;
use App\Entity\SchoolConfig;
use App\Entity\SchoolInterface;
use App\Repository\SchoolConfigRepository;
use App\Repository\SchoolRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;

/**
 * Class SetSchoolConfigValueCommandTest
 * @package App\Tests\Command
 * @group cli
 */
class SetSchoolConfigValueCommandTest extends KernelTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'ilios:set-school-config-value';

    protected $commandTester;
    protected $schoolRepository;
    protected $schoolConfigRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->schoolRepository = m::mock(SchoolRepository::class);
        $this->schoolConfigRepository = m::mock(SchoolConfigRepository::class);
        $command = new SetSchoolConfigValueCommand($this->schoolRepository, $this->schoolConfigRepository);
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
        unset($this->schoolRepository);
        unset($this->schoolConfigRepository);
        unset($this->commandTester);
    }

    public function testSaveExistingConfig()
    {
        $mockSchool = m::mock(SchoolInterface::class);
        $mockSchool->shouldReceive('getId')->once()->andReturn(1);
        $this->schoolRepository->shouldReceive('findOneBy')->once()->with(['id' => '1'])->andReturn($mockSchool);
        $mockConfig = m::mock(SchoolConfig::class);
        $mockConfig->shouldReceive('setValue')->with('bar')->once();
        $this->schoolConfigRepository->shouldReceive('findOneBy')
            ->with(['school' => '1', 'name' => 'foo'])
            ->once()
            ->andReturn($mockConfig);
        $this->schoolConfigRepository->shouldReceive('update')->with($mockConfig, true)->once();

        $this->commandTester->execute([
            'command'      => self::COMMAND_NAME,
            'school'         => '1',
            'name'         => 'foo',
            'value'        => 'bar',
        ]);
    }

    public function testSaveNewConfig()
    {
        $mockSchool = m::mock(SchoolInterface::class);
        $mockSchool->shouldReceive('getId')->once()->andReturn(1);
        $this->schoolRepository->shouldReceive('findOneBy')->once()->with(['id' => '1'])->andReturn($mockSchool);
        $mockConfig = m::mock(SchoolConfig::class);
        $mockConfig->shouldReceive('setValue')->with('bar')->once();
        $mockConfig->shouldReceive('setSchool')->with($mockSchool)->once();
        $mockConfig->shouldReceive('setName')->with('foo')->once();
        $this->schoolConfigRepository
            ->shouldReceive('findOneBy')
            ->with(['school' => '1', 'name' => 'foo'])
            ->once()
            ->andReturn(null);
        $this->schoolConfigRepository->shouldReceive('create')->once()->andReturn($mockConfig);
        $this->schoolConfigRepository->shouldReceive('update')->with($mockConfig, true)->once();

        $this->commandTester->execute([
            'command' => self::COMMAND_NAME,
            'school' => '1',
            'name' => 'foo',
            'value' => 'bar',
        ]);
    }

    public function testNameRequired()
    {
        $this->expectException(\RuntimeException::class);
        $this->commandTester->execute([
            'command'      => self::COMMAND_NAME,
            'school'         => '1',
            'value'        => 'bar',
        ]);
    }

    public function testValueRequired()
    {
        $this->expectException(\RuntimeException::class);
        $this->commandTester->execute([
            'command'      => self::COMMAND_NAME,
            'school'         => '1',
            'name'         => 'foo',
        ]);
    }

    public function testSchoolRequired()
    {
        $this->expectException(\RuntimeException::class);
        $this->commandTester->execute([
            'command'      => self::COMMAND_NAME,
            'name'         => 'foo',
            'value'         => 'bar',
        ]);
    }
}
