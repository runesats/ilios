<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * Alert controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class AlertControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        return [
            'Ilios\CoreBundle\Tests\Fixture\LoadAlertData',
            'Ilios\CoreBundle\Tests\Fixture\LoadAlertChangeTypeData',
            'Ilios\CoreBundle\Tests\Fixture\LoadUserData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSchoolData'
        ];
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [
        ];
    }

    public function testGetAlert()
    {
        $alert = $this->container
            ->get('ilioscore.dataloader.alert')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_alerts',
                ['id' => $alert['id']]
            )
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($alert),
            json_decode($response->getContent(), true)['alerts'][0]
        );
    }

    public function testGetAllAlerts()
    {
        $this->createJsonRequest('GET', $this->getUrl('cget_alerts'));
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.alert')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['alerts']
        );
    }

    public function testPostAlert()
    {
        $data = $this->container->get('ilioscore.dataloader.alert')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_alerts'),
            json_encode(['alert' => $postData])
        );

        $response = $this->client->getResponse();
        $headers  = [];

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(
            $data,
            json_decode($response->getContent(), true)['alerts'][0],
            $response->getContent()
        );
    }

    public function testPostBadAlert()
    {
        $invalidAlert = $this->container
            ->get('ilioscore.dataloader.alert')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_alerts'),
            json_encode(['alert' => $invalidAlert])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutAlert()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.alert')
            ->getOne();

        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_alerts',
                ['id' => $data['id']]
            ),
            json_encode(['alert' => $postData])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($data),
            json_decode($response->getContent(), true)['alert']
        );
    }

    public function testDeleteAlert()
    {
        $alert = $this->container
            ->get('ilioscore.dataloader.alert')
            ->getOne()
        ;

        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_alerts',
                ['id' => $alert['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->client->request(
            'GET',
            $this->getUrl(
                'get_alerts',
                ['id' => $alert['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testAlertNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_alerts', ['id' => '0'])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
