<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SyncFormerStudentsCommand;
use App\Entity\Manager\UserManager;
use App\Entity\Manager\UserRoleManager;
use App\Service\Directory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;

/**
 * Class SyncFormerStudentsCommandTest
 * @package App\Tests\Command
 * @group cli
 */
class SyncFormerStudentsCommandTest extends KernelTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'ilios:sync-former-students';

    protected $userManager;
    protected $userRoleManager;
    protected $commandTester;
    protected $questionHelper;
    protected $directory;

    public function setUp(): void
    {
        $this->userManager = m::mock(UserManager::class);
        $this->userRoleManager = m::mock(UserRoleManager::class);
        $this->directory = m::mock(Directory::class);

        $command = new SyncFormerStudentsCommand($this->userManager, $this->userRoleManager, $this->directory);
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add($command);
        $commandInApp = $application->find(self::COMMAND_NAME);
        $this->commandTester = new CommandTester($commandInApp);
        $this->questionHelper = $command->getHelper('question');
    }

    /**
     * Remove all mock objects
     */
    public function tearDown(): void
    {
        unset($this->userManager);
        unset($this->userRoleManager);
        unset($this->directory);
        unset($this->commandTester);
    }

    public function testExecute()
    {
        $fakeDirectoryUser1 = [
            'firstName' => 'first',
            'lastName' => 'last',
            'email' => 'email',
            'telephoneNumber' => 'phone',
            'campusId' => 'abc',
        ];
        $fakeDirectoryUser2 = [
            'firstName' => 'first2',
            'lastName' => 'last2',
            'email' => 'email2',
            'telephoneNumber' => 'phone2',
            'campusId' => 'abc2',
        ];
        $user = m::mock('App\Entity\UserInterface')
            ->shouldReceive('getId')->andReturn(42)
            ->shouldReceive('getFirstName')->andReturn('first')
            ->shouldReceive('getLastName')->andReturn('last')
            ->shouldReceive('getEmail')->andReturn('email')
            ->shouldReceive('getCampusId')->andReturn('abc')
            ->shouldReceive('isUserSyncIgnore')->andReturn(false)
            ->mock();
        $this->directory->shouldReceive('findByLdapFilter')
            ->with('FILTER')
            ->andReturn([$fakeDirectoryUser1, $fakeDirectoryUser2]);
        $this->userManager->shouldReceive('findUsersWhoAreNotFormerStudents')
            ->with(array('abc', 'abc2'))
            ->andReturn(new ArrayCollection([$user]));
        $this->userManager->shouldReceive('update')
            ->with($user, false);
        $role = m::mock('App\Entity\UserRoleInterface')
            ->shouldReceive('addUser')->with($user)
            ->mock();
        $user->shouldReceive('addRole')->with($role);
        $this->userRoleManager
            ->shouldReceive('findOneBy')
            ->with(array('title' => 'Former Student'))
            ->andReturn($role);
        $this->userRoleManager
            ->shouldReceive('update')
            ->with($role);

        $this->commandTester->setInputs(['Yes']);
        $this->commandTester->execute(array(
            'command'   => self::COMMAND_NAME,
            'filter'    => 'FILTER'
        ));

        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/Found 2 former students in the directory./',
            $output
        );

        $this->assertRegExp(
            '/There are 1 students in Ilios who will be marked as a Former Student./',
            $output
        );
        $this->assertRegExp(
            '/Do you wish to mark these users as Former Students?/',
            $output
        );
        $this->assertRegExp(
            '/abc\s+\| first\s+\| last\s+\| email /',
            $output
        );
        $this->assertRegExp(
            '/Former students updated successfully!/',
            $output
        );
    }

    public function testFilterRequired()
    {
        $this->expectException(\RuntimeException::class);
        $this->commandTester->execute(array('command' => self::COMMAND_NAME));
    }
}
