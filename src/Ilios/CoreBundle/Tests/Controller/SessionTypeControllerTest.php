<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * SessionType controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class SessionTypeControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        $fixtures = parent::getFixtures();
        return array_merge($fixtures, [
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionTypeData',
            'Ilios\CoreBundle\Tests\Fixture\LoadAssessmentOptionData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSchoolData',
            'Ilios\CoreBundle\Tests\Fixture\LoadAamcMethodData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionData'
        ]);
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [];
    }

    public function testGetSessionType()
    {
        $sessionType = $this->container
            ->get('ilioscore.dataloader.sessiontype')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_sessiontypes',
                ['id' => $sessionType['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $sessionType = $this->container->get('ilioscore.dataloader.session')
            ->removeDeletedSessionsFromArray(array($sessionType))[0];
        $this->assertEquals(
            $this->mockSerialize($sessionType),
            json_decode($response->getContent(), true)['sessionTypes'][0]
        );
    }

    public function testGetAllSessionTypes()
    {
        $sessionTypes = $this->container
            ->get('ilioscore.dataloader.sessiontype')
            ->getAll();
        $sessionTypes = $this->container->get('ilioscore.dataloader.session')
            ->removeDeletedSessionsFromArray($sessionTypes);
        
        $this->createJsonRequest(
            'GET',
            $this->getUrl('cget_sessiontypes'),
            null,
            $this->getAuthenticatedUserToken()
        );
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $sessionTypes
            ),
            json_decode($response->getContent(), true)['sessionTypes']
        );
    }

    public function testPostSessionType()
    {
        $data = $this->container->get('ilioscore.dataloader.sessiontype')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_sessiontypes'),
            json_encode(['sessionType' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(
            $data,
            json_decode($response->getContent(), true)['sessionTypes'][0],
            $response->getContent()
        );
    }

    public function testPostBadSessionType()
    {
        $invalidSessionType = $this->container
            ->get('ilioscore.dataloader.sessiontype')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_sessiontypes'),
            json_encode(['sessionType' => $invalidSessionType]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutSessionType()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.sessiontype')
            ->getOne();

        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_sessiontypes',
                ['id' => $data['id']]
            ),
            json_encode(['sessionType' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        
        $data = $this->container->get('ilioscore.dataloader.session')
            ->removeDeletedSessionsFromArray(array($data))[0];
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($data),
            json_decode($response->getContent(), true)['sessionType']
        );
    }

    public function testDeleteSessionType()
    {
        $sessionType = $this->container
            ->get('ilioscore.dataloader.sessiontype')
            ->getOne()
        ;

        $this->createJsonRequest(
            'DELETE',
            $this->getUrl(
                'delete_sessiontypes',
                ['id' => $sessionType['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_sessiontypes',
                ['id' => $sessionType['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testSessionTypeNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_sessiontypes', ['id' => '0']),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
