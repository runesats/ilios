<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\AuditLogExportCommand;
use App\Repository\AuditLogRepository;
use DateTime;
use DateTimeZone;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AuditLogExportCommandTest
 *
 * @link http://symfony.com/doc/current/components/console/introduction.html#testing-commands
 * @link http://symfony.com/doc/current/cookbook/console/console_command.html#testing-commands
 * @link http://www.ardianys.com/2013/04/symfony2-test-console-command-which-use.html
 * @group cli
 */
class AuditLogExportCommandTest extends KernelTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected $logger;

    protected $auditLogRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = m::mock(LoggerInterface::class);
        $this->auditLogRepository = m::mock(AuditLogRepository::class);
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = new AuditLogExportCommand($this->logger, $this->auditLogRepository);
        $application->add($command);

        $command = $application->find('ilios:export-audit-log');
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @covers \App\Command\InstallFirstUserCommand::execute
     */
    public function testExecuteWithDefaultRange()
    {
        $this->auditLogRepository
            ->shouldReceive('findInRange')
            ->withArgs(function (DateTime $from, DateTime $to) {
                $format = 'Y-m-d H:i:s';
                $midnightYesterday = new DateTime('midnight yesterday', new DateTimeZone('UTC'));
                $midnightToday = new DateTime('midnight today', new DateTimeZone('UTC'));
                return $from->format($format)  ===  $midnightYesterday->format($format)
                    && $to->format($format)  ===  $midnightToday->format($format);
            })
            ->andReturn([])
            ->once();

        $this->logger->shouldReceive('info');
        $this->commandTester->execute([]);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    /**
     * @covers \App\Command\InstallFirstUserCommand::execute
     */
    public function testExecuteWithCustomRange()
    {
        $this->auditLogRepository
            ->shouldReceive('findInRange')
            ->withArgs(function (DateTime $from, DateTime $to) {
                $format = 'Y-m-d';
                $twoYearsAgo = new DateTime('2 years ago', new DateTimeZone('UTC'));
                $twoDaysAgo = new DateTime('2 days ago', new DateTimeZone('UTC'));
                return $from->format($format)  ===  $twoYearsAgo->format($format)
                    && $to->format($format)  ===  $twoDaysAgo->format($format);
            })
            ->andReturn([])
            ->once();

        $this->logger->shouldReceive('info');
        $this->commandTester->execute([
            'from' => '2 years ago',
            'to' => '2 days ago',
        ]);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    /**
     * @covers \App\Command\InstallFirstUserCommand::execute
     */
    public function testExecuteCheckTableOutput()
    {
        $now = new DateTime();

        $this->auditLogRepository
            ->shouldReceive('findInRange')
            ->andReturn([
                [
                    'id' => '1',
                    'userId' => '10',
                    'action' => 'update',
                    'createdAt' => $now,
                    'objectId' => '20',
                    'valuesChanged' => 'phone',
                    'objectClass' => 'FooBar',
                ]
            ]);

        $this->logger->shouldReceive('info');
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $cleanNow = preg_quote($now->format('c'));
        $this->assertMatchesRegularExpression(
            "/1\s+\|\s+10\s+\|\s+update\s+\|\s+{$cleanNow}\s+\|\s+20\s+\|\s+FooBar\s+\|\s+phone/",
            $output
        );
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    /**
     * @covers \App\Command\InstallFirstUserCommand::execute
     */
    public function testExecuteCheckLogging()
    {
        $from = (new DateTime('midnight yesterday', new DateTimeZone('UTC')))->format('c');
        $to = (new DateTime('midnight today', new DateTimeZone('UTC')))->format('c');

        $this->auditLogRepository
            ->shouldReceive('findInRange')
            ->andReturn([
                [
                    'id' => '1',
                    'userId' => '10',
                    'action' => 'update',
                    'createdAt' => new DateTime(),
                    'objectId' => '20',
                    'valuesChanged' => 'phone',
                    'objectClass' => 'FooBar',
                ],
                [
                    'id' => '2',
                    'userId' => null,
                    'action' => 'insert',
                    'createdAt' => new DateTime(),
                    'objectId' => '21',
                    'valuesChanged' => 'email',
                    'objectClass' => 'Baz',
                ]
            ]);
        $this->auditLogRepository->shouldReceive('deleteInRange')->once();

        $this->logger->shouldReceive('info')->with('Starting Audit Log Export.')->once();
        $this->logger->shouldReceive('info')->with(
            "Exporting 2 audit log entries which were created between {$from} and {$to}."
        )->once();
        $this->logger->shouldReceive('info')->with(
            "Deleting all audit log entries that were created between {$from} and {$to}."
        )->once();
        $this->logger->shouldReceive('info')->with('Finished Audit Log Export.')->once();

        $this->commandTester->execute(['--delete' => 'true']);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    /**
     * @covers \App\Command\InstallFirstUserCommand::execute
     */

    public function testExecuteWithDeletion()
    {
        $midnightYesterday = new DateTime('midnight yesterday', new DateTimeZone('UTC'));
        $midnightToday = new DateTime('midnight today', new DateTimeZone('UTC'));

        $this->auditLogRepository
            ->shouldReceive('findInRange')
            ->andReturn([]);

        $this->logger->shouldReceive('info');

        $this->auditLogRepository
        ->shouldReceive('deleteInRange')
        ->withArgs(function (DateTime $from, DateTime $to) use ($midnightYesterday, $midnightToday) {
            $format = 'Y-m-d H:i:s';
            return $from->format($format)  ===  $midnightYesterday->format($format)
                && $to->format($format)  ===  $midnightToday->format($format);
        })->once();

        $this->commandTester->execute(['--delete' => 'true']);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}
