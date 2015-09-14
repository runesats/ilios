<?php
namespace Ilios\CliBundle\Tests\Command;

use Ilios\CliBundle\Command\SyncAllUsersCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;

class SyncAllUsersCommandTest extends \PHPUnit_Framework_TestCase
{
    const COMMAND_NAME = 'ilios:directory:sync-users';
    
    protected $userManager;
    protected $commandTester;
    protected $questionHelper;
    protected $directory;
    protected $authenticationService;
    
    public function setUp()
    {
        $this->userManager = m::mock('Ilios\CoreBundle\Entity\Manager\UserManagerInterface');
        $this->directory = m::mock('Ilios\CoreBundle\Service\Directory');
        $this->authenticationService = m::mock('Ilios\AuthenticationBundle\Service\AuthenticationInterface');
        
        $command = new SyncAllUsersCommand($this->userManager, $this->directory, $this->authenticationService);
        $application = new Application();
        $application->add($command);
        $commandInApp = $application->find(self::COMMAND_NAME);
        $this->commandTester = new CommandTester($commandInApp);
        $this->questionHelper = $command->getHelper('question');
        
    }

    /**
     * Remove all mock objects
     */
    public function tearDown()
    {
        unset($this->userManager);
        unset($this->directory);
        unset($this->authenticationService);
        unset($this->commandTester);
        m::close();
    }
    
    public function testExecuteUserWithNoChanges()
    {
        $this->userManager->shouldReceive('resetExaminedFlagForAllUsers')->once();
        $this->userManager->shouldReceive('getAllCampusIds')->with(false, false)->andReturn(['abc']);
        $fakeDirectoryUser = [
            'firstName' => 'first',
            'lastName' => 'last',
            'email' => 'email',
            'telephoneNumber' => 'phone',
            'campusId' => 'abc',
        ];
        $this->directory
            ->shouldReceive('findByCampusIds')
            ->with(['abc'])
            ->andReturn([$fakeDirectoryUser]);
        $user = m::mock('Ilios\CoreBundle\Entity\UserInterface')
            ->shouldReceive('getId')->andReturn(42)
            ->shouldReceive('getFirstAndLastName')->andReturn('first last')
            ->shouldReceive('getFirstName')->andReturn('first')
            ->shouldReceive('getLastName')->andReturn('last')
            ->shouldReceive('getEmail')->andReturn('email')
            ->shouldReceive('getPhone')->andReturn('phone')
            ->shouldReceive('getCampusId')->andReturn('abc')
            ->shouldReceive('setExamined')->with(true)
            ->mock();
        $this->userManager
            ->shouldReceive('findUserBy')
            ->with(array('campusId' => 'abc', 'enabled' => true, 'userSyncIgnore' => false))
            ->andReturn($user)
            ->once();
        $this->userManager->shouldReceive('updateUser')->with($user, false)->once();
                
        $this->commandTester->execute(array(
            'command'      => self::COMMAND_NAME
        ));
        
        
        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/Completed Sync Process 1 users found in the directory; 0 users updated./',
            $output
        );
    }
    
    public function testExecuteWithFirstNameChange()
    {
        $this->userManager->shouldReceive('resetExaminedFlagForAllUsers')->once();
        $this->userManager->shouldReceive('getAllCampusIds')->with(false, false)->andReturn(['abc']);
        $fakeDirectoryUser = [
            'firstName' => 'new-first',
            'lastName' => 'last',
            'email' => 'email',
            'telephoneNumber' => 'phone',
            'campusId' => 'abc',
        ];
        $this->directory
            ->shouldReceive('findByCampusIds')
            ->with(['abc'])
            ->andReturn([$fakeDirectoryUser]);
        $user = m::mock('Ilios\CoreBundle\Entity\UserInterface')
            ->shouldReceive('getId')->andReturn(42)
            ->shouldReceive('getFirstAndLastName')->andReturn('first last')
            ->shouldReceive('getFirstName')->andReturn('first')
            ->shouldReceive('getLastName')->andReturn('last')
            ->shouldReceive('getEmail')->andReturn('email')
            ->shouldReceive('getPhone')->andReturn('phone')
            ->shouldReceive('getCampusId')->andReturn('abc')
            ->shouldReceive('setExamined')->with(true)
            ->shouldReceive('setFirstName')->with('new-first')
            ->mock();
        $this->userManager
            ->shouldReceive('findUserBy')
            ->with(array('campusId' => 'abc', 'enabled' => true, 'userSyncIgnore' => false))
            ->andReturn($user)
            ->once();
        $this->userManager->shouldReceive('updateUser')->with($user, false)->once();
                
        $this->commandTester->execute(array(
            'command'      => self::COMMAND_NAME
        ));
        
        
        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/Updating first name from "first" to "new-first"/',
            $output
        );
        $this->assertRegExp(
            '/Completed Sync Process 1 users found in the directory; 1 users updated./',
            $output
        );
    }
    
    public function testExecuteWithLastNameChange()
    {
        $this->userManager->shouldReceive('resetExaminedFlagForAllUsers')->once();
        $this->userManager->shouldReceive('getAllCampusIds')->with(false, false)->andReturn(['abc']);
        $fakeDirectoryUser = [
            'firstName' => 'first',
            'lastName' => 'new-last',
            'email' => 'email',
            'telephoneNumber' => 'phone',
            'campusId' => 'abc',
        ];
        $this->directory
            ->shouldReceive('findByCampusIds')
            ->with(['abc'])
            ->andReturn([$fakeDirectoryUser]);
        $user = m::mock('Ilios\CoreBundle\Entity\UserInterface')
            ->shouldReceive('getId')->andReturn(42)
            ->shouldReceive('getFirstAndLastName')->andReturn('first last')
            ->shouldReceive('getFirstName')->andReturn('first')
            ->shouldReceive('getLastName')->andReturn('last')
            ->shouldReceive('getEmail')->andReturn('email')
            ->shouldReceive('getPhone')->andReturn('phone')
            ->shouldReceive('getCampusId')->andReturn('abc')
            ->shouldReceive('setExamined')->with(true)
            ->shouldReceive('setLastName')->with('new-last')
            ->mock();
        $this->userManager
            ->shouldReceive('findUserBy')
            ->with(array('campusId' => 'abc', 'enabled' => true, 'userSyncIgnore' => false))
            ->andReturn($user)
            ->once();
        $this->userManager->shouldReceive('updateUser')->with($user, false)->once();
                
        $this->commandTester->execute(array(
            'command'      => self::COMMAND_NAME
        ));
        
        
        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/Updating last name from "last" to "new-last"/',
            $output
        );
        $this->assertRegExp(
            '/Completed Sync Process 1 users found in the directory; 1 users updated./',
            $output
        );
    }
    
    public function testExecuteWithPhoneNumberChange()
    {
        $this->userManager->shouldReceive('resetExaminedFlagForAllUsers')->once();
        $this->userManager->shouldReceive('getAllCampusIds')->with(false, false)->andReturn(['abc']);
        $fakeDirectoryUser = [
            'firstName' => 'first',
            'lastName' => 'last',
            'email' => 'email',
            'telephoneNumber' => 'new-phone',
            'campusId' => 'abc',
        ];
        $this->directory
            ->shouldReceive('findByCampusIds')
            ->with(['abc'])
            ->andReturn([$fakeDirectoryUser]);
        $user = m::mock('Ilios\CoreBundle\Entity\UserInterface')
            ->shouldReceive('getId')->andReturn(42)
            ->shouldReceive('getFirstAndLastName')->andReturn('first last')
            ->shouldReceive('getFirstName')->andReturn('first')
            ->shouldReceive('getLastName')->andReturn('last')
            ->shouldReceive('getEmail')->andReturn('email')
            ->shouldReceive('getPhone')->andReturn('phone')
            ->shouldReceive('getCampusId')->andReturn('abc')
            ->shouldReceive('setExamined')->with(true)
            ->shouldReceive('setPhone')->with('new-phone')
            ->mock();
        $this->userManager
            ->shouldReceive('findUserBy')
            ->with(array('campusId' => 'abc', 'enabled' => true, 'userSyncIgnore' => false))
            ->andReturn($user)
            ->once();
        $this->userManager->shouldReceive('updateUser')->with($user, false)->once();
                
        $this->commandTester->execute(array(
            'command'      => self::COMMAND_NAME
        ));
        
        
        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/Updating phone number from "phone" to "new-phone"/',
            $output
        );
        $this->assertRegExp(
            '/Completed Sync Process 1 users found in the directory; 1 users updated./',
            $output
        );
    }
}
