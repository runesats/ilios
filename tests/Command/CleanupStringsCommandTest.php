<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CleanupStringsCommand;
use App\Entity\CourseObjectiveInterface;
use App\Entity\LearningMaterialInterface;
use App\Entity\Manager\CourseLearningMaterialManager;
use App\Entity\Manager\CourseObjectiveManager;
use App\Entity\Manager\LearningMaterialManager;
use App\Entity\Manager\ProgramYearObjectiveManager;
use App\Entity\Manager\SessionLearningMaterialManager;
use App\Entity\Manager\SessionManager;
use App\Entity\Manager\SessionObjectiveManager;
use App\Entity\ProgramYearObjectiveInterface;
use App\Entity\SessionInterface;
use App\Entity\SessionObjectiveInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use HTMLPurifier;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    protected $sessionObjectiveManager;
    protected $courseObjectiveManager;
    protected $programYearObjectiveManager;
    protected $learningMaterialManager;
    protected $courseLearningMaterialManager;
    protected $sessionLearningMaterialManager;
    protected $sessionManager;
    protected CommandTester $commandTester;
    protected HttpClientInterface $httpClient;

    public function setUp(): void
    {
        parent::setUp();
        $this->purifier = m::mock(HTMLPurifier::class);
        $this->sessionObjectiveManager = m::mock(SessionObjectiveManager::class);
        $this->courseObjectiveManager = m::mock(CourseObjectiveManager::class);
        $this->programYearObjectiveManager = m::mock(ProgramYearObjectiveManager::class);
        $this->learningMaterialManager = m::mock(LearningMaterialManager::class);
        $this->courseLearningMaterialManager = m::mock(CourseLearningMaterialManager::class);
        $this->sessionLearningMaterialManager = m::mock(SessionLearningMaterialManager::class);
        $this->sessionManager = m::mock(SessionManager::class);
        $this->em = m::mock(EntityManagerInterface::class);
        $this->httpClient = m::mock(HttpClientInterface::class);

        $command = new CleanupStringsCommand(
            $this->purifier,
            $this->em,
            $this->learningMaterialManager,
            $this->courseLearningMaterialManager,
            $this->sessionLearningMaterialManager,
            $this->sessionManager,
            $this->sessionObjectiveManager,
            $this->courseObjectiveManager,
            $this->programYearObjectiveManager,
            $this->httpClient
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
        unset($this->purifier);
        unset($this->em);
        unset($this->sessionObjectiveManager);
        unset($this->courseObjectiveManager);
        unset($this->programYearObjectiveManager);
        unset($this->learningMaterialManager);
        unset($this->courseLearningMaterialManager);
        unset($this->sessionLearningMaterialManager);
        unset($this->sessionManager);
        unset($this->commandTester);
        unset($this->httpClient);
    }

    public function testObjectiveTitle()
    {
        $cleanSessionObjective = m::mock(SessionObjectiveInterface::class)
            ->shouldReceive('getTitle')->andReturn('clean title')
            ->mock();
        $dirtySessionObjective = m::mock(SessionObjectiveInterface::class)
            ->shouldReceive('getTitle')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setTitle')->with('<h1>html title</h1>')
            ->mock();
        $this->sessionObjectiveManager->shouldReceive('findBy')->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$cleanSessionObjective, $dirtySessionObjective]);
        $this->sessionObjectiveManager->shouldReceive('update')->with($dirtySessionObjective, false);
        $this->sessionObjectiveManager->shouldReceive('getTotalObjectiveCount')->andReturn(2);

        $cleanCourseObjective = m::mock(CourseObjectiveInterface::class)
            ->shouldReceive('getTitle')->andReturn('clean title')
            ->mock();
        $dirtyCourseObjective = m::mock(CourseObjectiveInterface::class)
            ->shouldReceive('getTitle')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setTitle')->with('<h1>html title</h1>')
            ->mock();
        $this->courseObjectiveManager->shouldReceive('findBy')->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$cleanCourseObjective, $dirtyCourseObjective]);
        $this->courseObjectiveManager->shouldReceive('update')->with($dirtyCourseObjective, false);
        $this->courseObjectiveManager->shouldReceive('getTotalObjectiveCount')->andReturn(2);

        $cleanPyObjective = m::mock(ProgramYearObjectiveInterface::class)
            ->shouldReceive('getTitle')->andReturn('clean title')
            ->mock();
        $dirtyProgramYearObjective = m::mock(ProgramYearObjectiveInterface::class)
            ->shouldReceive('getTitle')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setTitle')->with('<h1>html title</h1>')
            ->mock();
        $this->programYearObjectiveManager->shouldReceive('findBy')->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$cleanPyObjective, $dirtyProgramYearObjective]);
        $this->programYearObjectiveManager->shouldReceive('update')->with($dirtyProgramYearObjective, false);
        $this->programYearObjectiveManager->shouldReceive('getTotalObjectiveCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')
            ->with('clean title')
            ->andReturn('clean title')
            ->times(3);
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html title</h1>')
            ->andReturn('<h1>html title</h1>')
            ->times(3);
        $this->em->shouldReceive('flush')->times(3);
        $this->em->shouldReceive('clear')->times(3);
        $this->commandTester->execute([
            'command'           => self::COMMAND_NAME,
            '--objective-title' => true
        ]);


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/3 Objective Titles updated/',
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
            ->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$clean, $dirty]);
        $this->learningMaterialManager->shouldReceive('update')->with($dirty, false);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean title')->andReturn('clean title');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html title</h1>')
            ->andReturn('<h1>html title</h1>');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();
        $this->commandTester->execute([
            'command'           => self::COMMAND_NAME,
            '--learningmaterial-description' => true
        ]);


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
            ->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$cleanCourse, $dirtyCourse]);
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
            ->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$cleanSession, $dirtySession]);
        $this->sessionLearningMaterialManager->shouldReceive('update')
            ->with($dirtySession, false);
        $this->sessionLearningMaterialManager->shouldReceive('getTotalSessionLearningMaterialCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean session note')->andReturn('clean session note');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html session note</h1>')
            ->andReturn('<h1>html session note</h1>');

        $this->em->shouldReceive('flush')->twice();
        $this->em->shouldReceive('clear')->twice();
        $this->commandTester->execute([
            'command'           => self::COMMAND_NAME,
            '--learningmaterial-note' => true
        ]);


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
        $clean = m::mock(SessionInterface::class)
            ->shouldReceive('getDescription')->andReturn('clean title')
            ->mock();
        $dirty = m::mock(SessionInterface::class)
            ->shouldReceive('getDescription')->andReturn('<script>alert();</script><h1>html title</h1>')
            ->shouldReceive('setDescription')->with('<h1>html title</h1>')
            ->mock();
        $this->sessionManager->shouldReceive('findBy')
            ->with([], ['id' => 'ASC'], 500, 0)
            ->andReturn([$clean, $dirty]);
        $this->sessionManager->shouldReceive('update')->with($dirty, false);
        $this->sessionManager->shouldReceive('getTotalSessionCount')->andReturn(2);

        $this->purifier->shouldReceive('purify')->with('clean title')->andReturn('clean title');
        $this->purifier->shouldReceive('purify')
            ->with('<script>alert();</script><h1>html title</h1>')
            ->andReturn('<h1>html title</h1>');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();
        $this->commandTester->execute([
            'command'           => self::COMMAND_NAME,
            '--session-description' => true
        ]);


        $output = $this->commandTester->getDisplay();
        $this->assertRegExp(
            '/1 Session Descriptions updated/',
            $output
        );
    }

    public function correctLearningMaterialLinksProvider(): array
    {
        return [
            ['iliosproject.org', 'https://iliosproject.org'],
            ['http//iliosproject.org', 'https://http//iliosproject.org'],
        ];
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     * @dataProvider correctLearningMaterialLinksProvider
     * @param string $link
     * @param string $fixedLink
     */
    public function testCorrectLearningMaterialLinks($link, $fixedLink)
    {
        $lm = m::mock(LearningMaterialInterface::class);
        $lm->shouldReceive('getLink')->andReturn($link);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn(1);
        $this->learningMaterialManager->shouldReceive('findBy')->andReturn([ $lm ]);
        $this->httpClient->shouldReceive('request')->once()->with('HEAD', $fixedLink);
        $lm->shouldReceive('setLink')->once()->with($fixedLink);
        $this->learningMaterialManager->shouldReceive('update')->once()->with($lm, false);
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();

        $this->commandTester->execute([ 'command' => self::COMMAND_NAME, '--learningmaterial-links' => true ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("1 learning material links updated, 0 failures.", $output);
    }

    public function correctLearningMaterialLinksWhithoutFetchingProvider(): array
    {
        return [
            [' http://iliosproject.org', 'http://iliosproject.org'],
            ['https://iliosproject.org    ', 'https://iliosproject.org'],
            [' ftps://iliosproject.org ', 'ftps://iliosproject.org'],
            ['  ftp://iliosproject.org ', 'ftp://iliosproject.org'],
            ['http://https://iliosproject.org', 'https://iliosproject.org'],
            ['http://http://iliosproject.org', 'http://iliosproject.org'],
            ['http://ftp://iliosproject.org', 'ftp://iliosproject.org'],
            ['http://ftps://iliosproject.org', 'ftps://iliosproject.org'],
        ];
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     * @dataProvider correctLearningMaterialLinksWhithoutFetchingProvider
     * @param string $link
     * @param string $fixedLink
     */
    public function testCorrectLearningMaterialLinksWithoutFetching($link, $fixedLink)
    {
        $lm = m::mock(LearningMaterialInterface::class);
        $lm->shouldReceive('getLink')->andReturn($link);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn(1);
        $this->learningMaterialManager->shouldReceive('findBy')->andReturn([ $lm ]);
        $this->httpClient->shouldNotReceive('request');
        $lm->shouldReceive('setLink')->once()->with($fixedLink);
        $this->learningMaterialManager->shouldReceive('update')->once()->with($lm, false);
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();

        $this->commandTester->execute([ 'command' => self::COMMAND_NAME, '--learningmaterial-links' => true ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("1 learning material links updated, 0 failures.", $output);
    }

    public function correctLearningMaterialLinksNoChangesProvider(): array
    {
        return [
            [null],
            [''],
            ['    '],
            ['http://iliosproject.org/'],
            ['https://iliosproject.org/'],
            ['ftp://iliosproject.org/'],
            ['ftps://iliosproject.org/'],
            ['HttPs://iliosproject.org/'],
        ];
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     * @dataProvider correctLearningMaterialLinksNoChangesProvider
     */
    public function testCorrectLearningMaterialLinksNoChanges($link)
    {
        $lm = m::mock(LearningMaterialInterface::class);
        $lm->shouldReceive('getLink')->andReturn($link);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn(1);
        $this->learningMaterialManager->shouldReceive('findBy')->andReturn([ $lm ]);
        $this->httpClient->shouldNotReceive('request');
        $lm->shouldNotReceive('setLink');
        $this->learningMaterialManager->shouldNotReceive('update');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();

        $this->commandTester->execute([ 'command' => self::COMMAND_NAME, '--learningmaterial-links' => true ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("0 learning material links updated, 0 failures.", $output);
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     */
    public function testCorrectLearningMaterialLinksInBulk()
    {
        $total = 1001;
        $lms = [];
        for ($i = 0; $i < $total; $i++) {
            $url = "iliosproject{$i}.org";
            $fixedUrl = 'https://' . $url;
            $lm = m::mock(LearningMaterialInterface::class);
            $lm->shouldReceive('getLink')->once()->andReturn($url);
            $lm->shouldReceive('setLink')->once()->with($fixedUrl);
            $lms[] = $lm;
        }
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn($total);
        $this->learningMaterialManager
            ->shouldReceive('findBy')
            ->with([], ['id' => 'ASC'], 500, 0)
            ->once()
            ->andReturn(array_slice($lms, 0, 500));
        $this->learningMaterialManager
            ->shouldReceive('findBy')
            ->with([], ['id' => 'ASC'], 500, 500)
            ->once()
            ->andReturn(array_slice($lms, 500, 500));
        $this->learningMaterialManager
            ->shouldReceive('findBy')
            ->with([], ['id' => 'ASC'], 500, 1000)
            ->once()
            ->andReturn(array_slice($lms, 1000));

        $this->httpClient->shouldReceive('request')->times($total);
        $this->learningMaterialManager->shouldReceive('update')->times($total);
        $this->em->shouldReceive('flush')->times(3);
        $this->em->shouldReceive('clear')->times(3);

        $this->commandTester->execute([ 'command' => self::COMMAND_NAME, '--learningmaterial-links' => true ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("{$total} learning material links updated, 0 failures.", $output);
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     */
    public function testCorrectLearningMaterialLinksFails()
    {
        $link = 'iliosproject.org';
        $lm = m::mock(LearningMaterialInterface::class);
        $lm->shouldReceive('getLink')->andReturn($link);
        $lm->shouldReceive('getId')->andReturn(1);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn(1);
        $this->learningMaterialManager->shouldReceive('findBy')->andReturn([ $lm ]);
        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('HEAD', 'https://' . $link)
            ->andThrow(new Exception());
        $this->httpClient->shouldReceive('request')->once()->with('HEAD', 'http://' . $link)->andThrow(new Exception());
        $lm->shouldNotReceive('setLink');
        $this->learningMaterialManager->shouldNotReceive('update');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();

        $this->commandTester->execute([ 'command' => self::COMMAND_NAME, '--learningmaterial-links' => true ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("0 learning material links updated, 1 failures.", $output);
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     */
    public function testCorrectLearningMaterialLinksFailsOnHttps()
    {
        $link = 'iliosproject.org';
        $fixedUrl = 'http://iliosproject.org';
        $lm = m::mock(LearningMaterialInterface::class);
        $lm->shouldReceive('getLink')->andReturn($link);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn(1);
        $this->learningMaterialManager->shouldReceive('findBy')->andReturn([ $lm ]);
        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('HEAD', 'https://' . $link)
            ->andThrow(new Exception());
        $this->httpClient->shouldReceive('request')->once()->with('HEAD', 'http://' . $link);
        $lm->shouldReceive('setLink')->once()->with($fixedUrl);
        $this->learningMaterialManager->shouldReceive('update')->once()->with($lm, false);
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();

        $this->commandTester->execute([ 'command' => self::COMMAND_NAME, '--learningmaterial-links' => true ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("1 learning material links updated, 0 failures.", $output);
    }

    /**
     * @covers \App\Command\CleanupStringsCommand::correctLearningMaterialLinks
     */
    public function testCorrectLearningMaterialLinksVerboseFailureOutput()
    {
        $link = 'iliosproject.org';
        $lm = m::mock(LearningMaterialInterface::class);
        $lm->shouldReceive('getLink')->andReturn($link);
        $lm->shouldReceive('getId')->andReturn(1);
        $this->learningMaterialManager->shouldReceive('getTotalLearningMaterialCount')->once()->andReturn(1);
        $this->learningMaterialManager->shouldReceive('findBy')->andReturn([ $lm ]);
        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('HEAD', 'https://' . $link)
            ->andThrow(new Exception());
        $this->httpClient->shouldReceive('request')
            ->once()
            ->with('HEAD', 'http://' . $link)
            ->andThrow(new Exception('FAIL!'));
        $lm->shouldNotReceive('setLink');
        $this->learningMaterialManager->shouldNotReceive('update');
        $this->em->shouldReceive('flush')->once();
        $this->em->shouldReceive('clear')->once();

        $this->commandTester->execute(
            [ 'command' => self::COMMAND_NAME,'--learningmaterial-links' => true ],
            [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE],
        );

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString("0 learning material links updated, 1 failures.", $output);
        $this->assertRegExp('/\| Learning Material ID\s+\| Link\s+\| Error Message\s+\|/', $output);
        $this->assertRegExp('/\| 1\s+\| iliosproject.org\s+\| FAIL!\s+\|/', $output);
    }
}
