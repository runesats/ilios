<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\Fixture\LoadAuthenticationData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Traits\JsonControllerTest;
use Faker\Factory as FakerFactory;

class ErrorControllerTest extends WebTestCase
{
    use JsonControllerTest;
    use FixturesTrait;

    /**
     * @var KernelBrowser
     */
    protected $kernelBrowser;

    public function setUp(): void
    {
        parent::setUp();
        $this->kernelBrowser = self::createClient();
        $this->loadFixtures([
            LoadAuthenticationData::class,
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->kernelBrowser);
        unset($this->fixtures);
    }

    public function testIndex()
    {
        $faker = FakerFactory::create();

        $data = [
            'mainMessage' => $faker->text(100),
            'stack' => $faker->text(1000)
        ];
        $this->makeJsonRequest(
            $this->kernelBrowser,
            'POST',
            '/errors',
            json_encode(['data' => json_encode($data)]),
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());
    }
}
