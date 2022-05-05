<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Command\UpdateFrontendCommand;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Filesystem\Filesystem;
use DateTime;

class IndexControllerTest extends WebTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private const COMMAND_NAME = 'ilios:maintenance:update-frontend';
    private const TEST_API_VERSION = '33.14-test';

    protected string $jsonPath;
    protected Filesystem $fileSystem;
    protected KernelBrowser $kernelBrowser;

    /**
     * @var array
     */
    protected $testFiles = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->kernelBrowser = static::createClient();
        $container = $this->kernelBrowser->getContainer();
        $projectDir = $container->getParameter('kernel.project_dir');
        $this->jsonPath = UpdateFrontendCommand::getActiveFrontendIndexPath($projectDir);
        $this->fileSystem = new Filesystem();
        $this->testFiles = [];
    }

    /**
     * Remove all mock objects
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->kernelBrowser);
        foreach ($this->testFiles as $path) {
            $this->fileSystem->remove($path);
        }
        unset($this->fs);
        unset($this->fileSystem);
    }

    public function testIndex()
    {
        $json = json_encode([
            'meta' => [],
            'link' => [],
            'script' => [],
            'style' => [],
            'noScript' => [],
            'div' => [],
        ]);
        $this->setupTestFile($this->jsonPath, $json, false);
        $this->kernelBrowser->request('GET', '/');
        $response = $this->kernelBrowser->getResponse();

        $this->assertStringContainsString('<title>Ilios</title>', $response->getContent());
        $this->assertStringContainsString(
            '<meta name=\'iliosconfig-error-capture-enabled\' content="false">',
            $response->getContent()
        );

        $this->assertTrue(
            $response->headers->getCacheControlDirective('no-cache'),
            'cache headers are correct'
        );
        $this->assertEquals(
            null,
            $response->headers->get('Content-Encoding'),
            'content encoding headers are correct'
        );
    }

    public function testGzippedWhenRequested()
    {
        $json = json_encode([
            'meta' => [],
            'link' => [],
            'script' => [],
            'style' => [],
            'noScript' => [],
            'div' => [],
        ]);
        $this->setupTestFile($this->jsonPath, $json, false);
        $this->kernelBrowser->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_ACCEPT_ENCODING' => 'deflate, gzip, br']
        );
        $response = $this->kernelBrowser->getResponse();


        $this->assertTrue(
            $response->headers->getCacheControlDirective('no-cache'),
            'cache headers are correct'
        );
        $this->assertEquals(
            'gzip',
            $response->headers->get('Content-Encoding'),
            'content encoding headers are correct' . var_export($response->getContent(), true)
        );
        $content = $response->getContent();
        $inflatedContent = gzdecode($content);
        $this->assertStringContainsString('<title>Ilios</title>', $inflatedContent);
    }

    public function testIndexFromCacheIsTheSameInGzippedAndUnCompressed()
    {
        $json = json_encode([
            'meta' => [],
            'link' => [],
            'script' => [],
            'style' => [],
            'noScript' => [],
            'div' => [],
        ]);
        $this->setupTestFile($this->jsonPath, $json, false);

        $this->kernelBrowser->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_ACCEPT_ENCODING' => 'deflate, gzip, br']
        );
        $response = $this->kernelBrowser->getResponse();
        $gzipEtag = $response->getEtag();

        $this->kernelBrowser->request(
            'GET',
            '/'
        );
        $response = $this->kernelBrowser->getResponse();
        $uncompressedEtag = $response->getEtag();

        $this->assertEquals($gzipEtag, $uncompressedEtag);


        $this->kernelBrowser->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_ACCEPT_ENCODING' => 'deflate, gzip, br', 'HTTP_IF_NONE_MATCH' => $uncompressedEtag]
        );
        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(304, $response->getStatusCode(), 'Wrong Status Code');
        $this->assertEmpty($response->getContent());

        $this->kernelBrowser->request(
            'GET',
            '/',
            [],
            [],
            ['HTTP_IF_NONE_MATCH' => $gzipEtag]
        );
        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(304, $response->getStatusCode(), 'Wrong Status Code');
        $this->assertEmpty($response->getContent());
    }

    public function testErrorCaptureConfiguration()
    {
        $json = json_encode([
            'meta' => [],
            'link' => [],
            'script' => [],
            'style' => [],
            'noScript' => [],
            'div' => [],
        ]);
        $orig = $_ENV['ILIOS_ERROR_CAPTURE_ENABLED'];
        $_ENV['ILIOS_ERROR_CAPTURE_ENABLED'] = true;
        $this->setupTestFile($this->jsonPath, $json, false);
        $this->kernelBrowser->request('GET', '/');
        $response = $this->kernelBrowser->getResponse();

        $this->assertStringContainsString(
            '<meta name=\'iliosconfig-error-capture-enabled\' content="true">',
            $response->getContent()
        );
        $_ENV['ILIOS_ERROR_CAPTURE_ENABLED'] = $orig;
    }

    protected function setupTestFile(string $path, string $contents, bool $compressContents)
    {
        $this->testFiles[] = $path;
        if ($compressContents) {
            $contents = gzencode($contents);
        }
        $this->fileSystem->dumpFile($path, $contents);
    }
}
