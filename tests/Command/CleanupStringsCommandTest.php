<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CleanupStringsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;

/**
 * Class CleanupStringsCommandTest
 * @package App\Tests\Command
 * @group cli
 */
class CleanupStringsCommandTest extends KernelTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'ilios:cleanup-strings';

    protected $purifier;
    protected $em;
    protected $objectiveManager;
    protected $learningMaterialManager;
    protected $courseLearningMaterialManager;
    protected $sessionLearningMaterialManager;
    protected $sessionDescriptionManager;
    protected $commandTester;

    public function setUp()
    {
        $this->purifier = m::mock('HTMLPurifier');
        $this->objectiveManager = m::mock('App\Entity\Manager\ObjectiveManager');
        $this->learningMaterialManager = m::mock('App\Entity\Manager\LearningMaterialManager');
        $this->courseLearningMaterialManager = m::mock(
            'App\Entity\Manager\CourseLearningMaterialManager'
        );
        $this->sessionLearningMaterialManager = m::mock(
            'App\Entity\Manager\SessionLearningMaterialManager'
        );
        $this->sessionDescriptionManager = m::mock(
            'App\Entity\Manager\SessionDescriptionManager'
        );
        $this->em = m::mock(EntityManagerInterface::class);

        $command = new CleanupStringsCommand(
            $this->purifier,
            $this->em,
            $this->objectiveManager,
            $this->learningMaterialManager,
            $this->courseLearningMaterialManager,
            $this->sessionLearningMaterialManager,
            $this->sessionDescriptionManager
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
        unset($this->purifier);
        unset($this->em);
        unset($this->objectiveManager);
        unset($this->learningMaterialManager);
        unset($this->courseLearningMaterialManager);
        unset($this->sessionLearningMaterialManager);
        unset($this->sessionDescriptionManager);
        unset($this->commandTester);
    }

    public function testObjectiveTitle()
    {
        $cleanObjective = m::mock('App\Entity\ObjectiveInterface')
            ->shouldReceive('getTitle')->andReturn('clean title')
            ->mock();
        $dirtyObjective = m::mock('App\Entity\ObjectiveInterface')
            ->shouldReceive('getTitle')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setTitle')->with('<h1>html title</h1>')
            ->mock();
        $this->objectiveManager->shouldReceive('findBy')->with(array(), array('id' => 'ASC'), 500, 1)
            ->andReturn(array($cleanObjective, $dirtyObjective));
        $this->objectiveManager->shouldReceive('update')->with($dirtyObjective, false);
        $this->objectiveManager->shouldReceive('getTotalObjectiveCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean title')->andReturn('clean title');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html title</h1>')
            ->andReturn('<h1>html title</h1>');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();
        $this->commandTester->execute(array(
            'command'           => self::COMMAND_NAME,
            '--objective-title' => true
        ));


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/1 Objective Titles updated/',
            $output
        );
    }

    public function testLearningMaterialDescription()
    {
        $clean = m::mock('App\Entity\LearningMaterialInterface')
            ->shouldReceive('getDescription')->andReturn('clean title')
            ->mock();
        $dirty = m::mock('App\Entity\LearningMaterialInterface')
            ->shouldReceive('getDescription')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setDescription')->with('<h1>html title</h1>')
            ->mock();
        $this->learningMaterialManager->shouldReceive('findBy')
            ->with(array(), array('id' => 'ASC'), 500, 1)
            ->andReturn(array($clean, $dirty));
        $this->learningMaterialManager->shouldReceive('update')->with($dirty, false);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean title')->andReturn('clean title');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html title</h1>')
            ->andReturn('<h1>html title</h1>');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();
        $this->commandTester->execute(array(
            'command'           => self::COMMAND_NAME,
            '--learningmaterial-description' => true
        ));


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/1 Learning Material Descriptions updated/',
            $output
        );
    }

    public function testLearningMaterialNotes()
    {
        $cleanCourse = m::mock('App\Entity\CourseLearningMaterialInterface')
            ->shouldReceive('getNotes')->andReturn('clean course note')
            ->mock();
        $dirtyCourse = m::mock('App\Entity\CourseLearningMaterialInterface')
            ->shouldReceive('getNotes')->andReturn('<script>alert();</script><h1>html course note</h1>')
            ->shouldReceive('setNotes')->with('<h1>html course note</h1>')
            ->mock();
        $this->courseLearningMaterialManager->shouldReceive('findBy')
            ->with(array(), array('id' => 'ASC'), 500, 1)
            ->andReturn(array($cleanCourse, $dirtyCourse));
        $this->courseLearningMaterialManager->shouldReceive('update')->with($dirtyCourse, false);
        $this->courseLearningMaterialManager->shouldReceive('getTotalCourseLearningMaterialCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean course note')->andReturn('clean course note');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html course note</h1>')
            ->andReturn('<h1>html course note</h1>');


        $cleanSession = m::mock('App\Entity\SessionLearningMaterialInterface')
            ->shouldReceive('getNotes')->andReturn('clean session note')
            ->mock();
        $dirtySession = m::mock('App\Entity\SessionLearningMaterialInterface')
            ->shouldReceive('getNotes')->andReturn('<script>alert();</script><h1>html session note</h1>')
            ->shouldReceive('setNotes')->with('<h1>html session note</h1>')
            ->mock();
        $this->sessionLearningMaterialManager->shouldReceive('findBy')
            ->with(array(), array('id' => 'ASC'), 500, 1)
            ->andReturn(array($cleanSession, $dirtySession));
        $this->sessionLearningMaterialManager->shouldReceive('update')
            ->with($dirtySession, false);
        $this->sessionLearningMaterialManager->shouldReceive('getTotalSessionLearningMaterialCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean session note')->andReturn('clean session note');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html session note</h1>')
            ->andReturn('<h1>html session note</h1>');

        $this->em->shouldReceive('flush')->twice();
        $this->em->shouldReceive('clear')->twice();
        $this->commandTester->execute(array(
            'command'           => self::COMMAND_NAME,
            '--learningmaterial-note' => true
        ));


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/1 Course Learning Material Notes updated/',
            $output
        );


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/1 Session Learning Material Notes updated/',
            $output
        );
    }

    public function testSessionDescription()
    {
        $clean = m::mock('App\Entity\SessionDescriptionInterface')
            ->shouldReceive('getDescription')->andReturn('clean title')
            ->mock();
        $dirty = m::mock('App\Entity\SessionDescriptionInterface')
            ->shouldReceive('getDescription')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setDescription')->with('<h1>html title</h1>')
            ->mock();
        $this->sessionDescriptionManager->shouldReceive('findBy')
            ->with(array(), array('id' => 'ASC'), 500, 1)
            ->andReturn(array($clean, $dirty));
        $this->sessionDescriptionManager->shouldReceive('update')->with($dirty, false);
        $this->sessionDescriptionManager->shouldReceive('getTotalSessionDescriptionCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean title')->andReturn('clean title');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html title</h1>')
            ->andReturn('<h1>html title</h1>');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();
        $this->commandTester->execute(array(
            'command'           => self::COMMAND_NAME,
            '--session-description' => true
        ));


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/1 Session Descriptions updated/',
            $output
        );
    }
}
