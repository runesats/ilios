<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * LearnerGroup controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class LearnerGroupControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        $fixtures = parent::getFixtures();
        return array_merge($fixtures, [
            'Ilios\CoreBundle\Tests\Fixture\LoadLearnerGroupData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCohortData',
            'Ilios\CoreBundle\Tests\Fixture\LoadIlmSessionData',
            'Ilios\CoreBundle\Tests\Fixture\LoadOfferingData',
            'Ilios\CoreBundle\Tests\Fixture\LoadInstructorGroupData',
            'Ilios\CoreBundle\Tests\Fixture\LoadUserData',
        ]);
    }

    /**
     * @return array|string
     */
    protected function getPrivateFields()
    {
        return [
        ];
    }

    public function testGetLearnerGroup()
    {
        $learnerGroup = $this->container
            ->get('ilioscore.dataloader.learnergroup')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_learnergroups',
                ['id' => $learnerGroup['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($learnerGroup),
            json_decode($response->getContent(), true)['learnerGroups'][0]
        );
    }

    public function testGetAllLearnerGroups()
    {
        $this->createJsonRequest('GET', $this->getUrl('cget_learnergroups'), null, $this->getAuthenticatedUserToken());
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.learnergroup')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['learnerGroups']
        );
    }

    public function testPostLearnerGroup()
    {
        $data = $this->container->get('ilioscore.dataloader.learnergroup')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        unset($postData['children']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_learnergroups'),
            json_encode(['learnerGroup' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(
            $data,
            json_decode($response->getContent(), true)['learnerGroups'][0],
            $response->getContent()
        );
    }

    public function testPostLearnerGroupIlmSession()
    {
        $data = $this->container->get('ilioscore.dataloader.learnergroup')->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        unset($postData['children']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_learnergroups'),
            json_encode(['learnerGroup' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $newId = json_decode($this->client->getResponse()->getContent(), true)['learnerGroups'][0]['id'];
        foreach ($postData['ilmSessions'] as $id) {
            $this->createJsonRequest(
                'GET',
                $this->getUrl(
                    'get_ilmsessions',
                    ['id' => $id]
                ),
                null,
                $this->getAuthenticatedUserToken()
            );
            $data = json_decode($this->client->getResponse()->getContent(), true)['ilmSessions'][0];
            $this->assertTrue(in_array($newId, $data['learnerGroups']));
        }
    }

    public function testPostLearnerGroupOffering()
    {
        $data = $this->container->get('ilioscore.dataloader.learnergroup')->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        unset($postData['children']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_learnergroups'),
            json_encode(['learnerGroup' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $newId = json_decode($this->client->getResponse()->getContent(), true)['learnerGroups'][0]['id'];
        foreach ($postData['offerings'] as $id) {
            $this->createJsonRequest(
                'GET',
                $this->getUrl(
                    'get_offerings',
                    ['id' => $id]
                ),
                null,
                $this->getAuthenticatedUserToken()
            );
            $data = json_decode($this->client->getResponse()->getContent(), true)['offerings'][0];
            $this->assertTrue(in_array($newId, $data['learnerGroups']));
        }
    }

    public function testPostBadLearnerGroup()
    {
        $invalidLearnerGroup = $this->container
            ->get('ilioscore.dataloader.learnergroup')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_learnergroups'),
            json_encode(['learnerGroup' => $invalidLearnerGroup]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutLearnerGroup()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.learnergroup')
            ->getOne();

        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        unset($postData['children']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_learnergroups',
                ['id' => $data['id']]
            ),
            json_encode(['learnerGroup' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($data),
            json_decode($response->getContent(), true)['learnerGroup']
        );
    }

    public function testDeleteLearnerGroup()
    {
        $learnerGroup = $this->container
            ->get('ilioscore.dataloader.learnergroup')
            ->getOne()
        ;

        $this->createJsonRequest(
            'DELETE',
            $this->getUrl(
                'delete_learnergroups',
                ['id' => $learnerGroup['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_learnergroups',
                ['id' => $learnerGroup['id']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testLearnerGroupNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_learnergroups', ['id' => '0']),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
