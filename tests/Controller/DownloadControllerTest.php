<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\DataLoader\ApplicationConfigData;
use App\Tests\DataLoader\LearningMaterialData;
use App\Tests\Fixture\LoadApplicationConfigData;
use App\Tests\Fixture\LoadAuthenticationData;
use App\Tests\Fixture\LoadCourseLearningMaterialData;
use App\Tests\Fixture\LoadOfferingData;
use App\Tests\Fixture\LoadSessionDescriptionData;
use App\Tests\Fixture\LoadSessionLearningMaterialData;
use App\Tests\GetUrlTrait;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Traits\JsonControllerTest;

/**
 * Download controller Test.
 * @group other
 */
class DownloadControllerTest extends WebTestCase
{
    use JsonControllerTest;
    use FixturesTrait;
    use GetUrlTrait;

    /**
     * @var ProxyReferenceRepository
     */
    protected $fixtures;

    /**
     * @var KernelBrowser
     */
    protected $kernelBrowser;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->kernelBrowser = self::createClient();
        $this->fixtures = $this->loadFixtures([
            LoadAuthenticationData::class,
            LoadOfferingData::class,
            LoadCourseLearningMaterialData::class,
            LoadSessionLearningMaterialData::class,
            LoadSessionDescriptionData::class,
            LoadApplicationConfigData::class,
        ])->getReferenceRepository();
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->kernelBrowser);
        unset($this->fixtures);
    }

    public function testDownloadLearningMaterial()
    {
        /* @var array $learningMaterials */
        $learningMaterials = $this->kernelBrowser->getContainer()
            ->get(LearningMaterialData::class)
            ->getAll();
        $fileLearningMaterials = array_filter($learningMaterials, function ($arr) {
            return !empty($arr['filesize']);
        });
        $learningMaterial = array_values($fileLearningMaterials)[0];
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'GET',
            $this->getUrl(
                $this->kernelBrowser,
                'ilios_api_learningmaterial_get',
                ['version' => 'v1', 'object' => 'learningmaterials', 'id' => $learningMaterial['id']]
            ),
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );
        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);
        $data = json_decode($response->getContent(), true)['learningMaterials'][0];

        $this->kernelBrowser->request(
            'GET',
            $data['absoluteFileUri']
        );

        $response = $this->kernelBrowser->getResponse();

        $this->assertEquals(
            $response->headers->get('Content-Disposition'),
            'attachment; filename="' . $data['filename'] . '"'
        );
        $this->assertEquals(RESPONSE::HTTP_OK, $response->getStatusCode(), $response->getContent());
        $learningMaterialLoaderPath = realpath(__DIR__ . '/../Fixture/LoadLearningMaterialData.php');
        $this->assertEquals(file_get_contents($learningMaterialLoaderPath), $response->getContent());
    }

    public function testPdfInlineDownload()
    {
        $learningMaterial = $this->fixtures->getReference('learningMaterials4');

        $this->makeJsonRequest(
            $this->kernelBrowser,
            'GET',
            $this->getUrl(
                $this->kernelBrowser,
                'ilios_api_learningmaterial_get',
                ['version' => 'v1', 'object' => 'learningmaterials', 'id' => $learningMaterial->getId()]
            ),
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );
        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);
        $data = json_decode($response->getContent(), true)['learningMaterials'][0];

        $this->kernelBrowser->request(
            'GET',
            $data['absoluteFileUri'] . '?inline=true'
        );

        $response = $this->kernelBrowser->getResponse();

        $this->assertEquals(
            $response->headers->get('Content-Disposition'),
            'inline'
        );
    }

    public function testBadLearningMaterialToken()
    {
        //sending bad hash
        $this->kernelBrowser->request(
            'GET',
            '/lm/a7a8e202e9655ab81155c4c3e52b95098fcaa1c975f63f0327b467a981f6428f'
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(
            RESPONSE::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
    }

    protected function setLearningMaterialsDisabled(string $setTo)
    {
        $container = $this->kernelBrowser->getContainer();
        $config = $container->get(ApplicationConfigData::class)->getOne();
        $config['name'] = 'learningMaterialsDisabled';
        $config['value'] = $setTo;
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                'ilios_api_post',
                ['version' => 'v1', 'object' => 'applicationconfigs']
            ),
            json_encode(['applicationConfig' => $config]),
            $this->getTokenForUser($this->kernelBrowser, 2)
        );
        $this->assertJsonResponse($this->kernelBrowser->getResponse(), Response::HTTP_CREATED);
    }

    public function testDisabledMaterialsWithLM()
    {
        $this->setLearningMaterialsDisabled('true');

        $learningMaterial = $this->fixtures->getReference('learningMaterials4');
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'GET',
            $this->getUrl(
                $this->kernelBrowser,
                'ilios_api_learningmaterial_get',
                ['version' => 'v1', 'object' => 'learningmaterials', 'id' => $learningMaterial->getId()]
            ),
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );
        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_OK);
        $data = json_decode($response->getContent(), true)['learningMaterials'][0];

        $this->kernelBrowser->request(
            'GET',
            $data['absoluteFileUri'] . '?inline=true'
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->assertEquals(
            'Learning Materials are disabled on this instance.',
            $response->getContent()
        );
    }

    public function testDisabledMaterialsWithMissingLm()
    {
        $this->setLearningMaterialsDisabled('true');
        $this->kernelBrowser->request(
            'GET',
            '/lm/a7a8e202e9655ab81155c4c3e52b95098fcaa1c975f63f0327b467a981f6428f'
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->assertEquals(
            'Learning Materials are disabled on this instance.',
            $response->getContent()
        );
    }

    public function testBadLearningMaterialTokenWithLmEnabled()
    {
        $this->setLearningMaterialsDisabled('false');
        //sending bad hash
        $this->kernelBrowser->request(
            'GET',
            '/lm/a7a8e202e9655ab81155c4c3e52b95098fcaa1c975f63f0327b467a981f6428f'
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(
            RESPONSE::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
    }
}
