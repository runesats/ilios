<?php

namespace Tests\CoreBundle\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * CurriculumInventoryInstitution controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class CurriculumInventoryInstitutionControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        $fixtures = parent::getFixtures();
        return array_merge($fixtures, [
            'Tests\CoreBundle\Fixture\LoadCurriculumInventoryInstitutionData',
            'Tests\CoreBundle\Fixture\LoadSchoolData',
            'Tests\CoreBundle\Fixture\LoadPermissionData',
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

    /**
     * @group controllers_a
     */
    public function testGetCurriculumInventoryInstitution()
    {
        $curriculumInventoryInstitution = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryinstitution')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_curriculuminventoryinstitutions',
                ['id' => $curriculumInventoryInstitution['school']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($curriculumInventoryInstitution),
            json_decode($response->getContent(), true)['curriculumInventoryInstitutions'][0]
        );
    }

    /**
     * @group controllers_a
     */
    public function testGetAllCurriculumInventoryInstitutions()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('cget_curriculuminventoryinstitutions'),
            null,
            $this->getAuthenticatedUserToken()
        );
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.curriculuminventoryinstitution')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['curriculumInventoryInstitutions']
        );
    }

    /**
     * @group controllers_a
     */
    public function testPostCurriculumInventoryInstitution()
    {
        $data = $this->container->get('ilioscore.dataloader.curriculuminventoryinstitution')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_curriculuminventoryinstitutions'),
            json_encode(['curriculumInventoryInstitution' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(
            $data,
            json_decode($response->getContent(), true)['curriculumInventoryInstitutions'][0],
            $response->getContent()
        );
    }

    /**
     * @group controllers_a
     */
    public function testPostBadCurriculumInventoryInstitution()
    {
        $invalidCurriculumInventoryInstitution = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryinstitution')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_curriculuminventoryinstitutions'),
            json_encode(['curriculumInventoryInstitution' => $invalidCurriculumInventoryInstitution]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @group controllers_a
     */
    public function testPutCurriculumInventoryInstitution()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryinstitution')
            ->getOne();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_curriculuminventoryinstitutions',
                ['id' => $data['id']]
            ),
            json_encode(['curriculumInventoryInstitution' => $postData]),
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($data),
            json_decode($response->getContent(), true)['curriculumInventoryInstitution']
        );
    }

    /**
     * @group controllers
     */
    public function testDeleteCurriculumInventoryInstitution()
    {
        $curriculumInventoryInstitution = $this->container
            ->get('ilioscore.dataloader.curriculuminventoryinstitution')
            ->getOne()
        ;

        $this->createJsonRequest(
            'DELETE',
            $this->getUrl(
                'delete_curriculuminventoryinstitutions',
                ['id' => $curriculumInventoryInstitution['school']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_curriculuminventoryinstitutions',
                ['id' => $curriculumInventoryInstitution['school']]
            ),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @group controllers
     */
    public function testCurriculumInventoryInstitutionNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_curriculuminventoryinstitutions', ['id' => '0']),
            null,
            $this->getAuthenticatedUserToken()
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
