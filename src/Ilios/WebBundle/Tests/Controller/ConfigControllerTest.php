<?php
namespace Ilios\WebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use FOS\RestBundle\Util\Codes;
use Ilios\CoreBundle\Tests\Traits\JsonControllerTest;

/**
 * Class ConfigControllerTest
 * @package Ilios\WebBundle\Tests\Controller
 */
class ConfigControllerTest extends WebTestCase
{
    use JsonControllerTest;
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/application/config');

        $response = $client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);

        $this->assertEquals(
            array('config' => array('type' => 'form', 'locale' => 'en', 'userSearchType' => 'local')),
            json_decode($response->getContent(), true)
        );
    }
}
