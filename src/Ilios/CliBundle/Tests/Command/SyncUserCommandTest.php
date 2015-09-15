<?php
namespace Ilios\CliBundle\Tests\Command;

use Ilios\CliBundle\Command\SyncUserCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;

class SyncUserCommandTest extends \PHPUnit_Framework_TestCase
{
    const COMMAND_NAME = 'ilios:directory:sync-user';
    
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
        
        $command = new SyncUserCommand($this->userManager, $this->directory, $this->authenticationService);
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
    
    public function testExecute()
    {
        $user = m::mock('Ilios\CoreBundle\Entity\UserInterface')
            ->shouldReceive('getFirstName')->andReturn('old-first')
            ->shouldReceive('getLastName')->andReturn('old-last')
            ->shouldReceive('getEmail')->andReturn('old-email')
            ->shouldReceive('getPhone')->andReturn('old-phone')
            ->shouldReceive('getCampusId')->andReturn('abc')
            ->shouldReceive('setFirstName')->with('first')
            ->shouldReceive('setLastName')->with('last')
            ->shouldReceive('setEmail')->with('email')
            ->shouldReceive('setPhone')->with('phone')
            ->mock();
        $this->userManager->shouldReceive('findUserBy')->with(array('id' => 1))->andReturn($user);
        $this->userManager->shouldReceive('updateUser')->with($user);
        $fakeDirectoryUser = [
            'firstName' => 'first',
            'lastName' => 'last',
            'email' => 'email',
            'telephoneNumber' => 'phone',
            'campusId' => 'abc',
        ];
        $this->authenticationService->shouldReceive('syncUser')->with($fakeDirectoryUser, $user)->once();
        $this->directory->shouldReceive('findByCampusId')->with('abc')->andReturn($fakeDirectoryUser);
        $this->sayYesWhenAsked();
        
        $this->commandTester->execute(array(
            'command'      => self::COMMAND_NAME,
            'userId'         => '1'
        ));
        
        
        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/Ilios User     | abc       | old-first | old-last | old-email | old-phone/',
            $output
        );
        $this->assertRegExp(
            '/Directory User | abc       | first     | last     | email     | phone/',
            $output
        );
    }
    
    public function testBadUserId()
    {
        $this->userManager->shouldReceive('findUserBy')->with(array('id' => 1))->andReturn(null);
        $this->setExpectedException('Exception', 'No user with id #1');
        $this->commandTester->execute(array(
            'command'      => self::COMMAND_NAME,
            'userId'         => '1'
        ));
        
    }
    
    public function testUserRequired()
    {
        $this->setExpectedException('RuntimeException');
        $this->commandTester->execute(array('command' => self::COMMAND_NAME));
    }

    protected function sayYesWhenAsked()
    {
        $stream = fopen('php://memory', 'r+', false);
        
        fputs($stream, 'Yes\\n');
        rewind($stream);
        
        $this->questionHelper->setInputStream($stream);
    }
}
