<?php

namespace Ilios\CoreBundle\Tests\Controller;

use FOS\RestBundle\Util\Codes;

/**
 * Course controller Test.
 * @package Ilios\CoreBundle\Test\Controller;
 */
class CourseControllerTest extends AbstractControllerTest
{
    /**
     * @return array|string
     */
    protected function getFixtures()
    {
        return [
            'Ilios\CoreBundle\Tests\Fixture\LoadCourseData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCourseClerkshipTypeData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSchoolData',
            'Ilios\CoreBundle\Tests\Fixture\LoadPublishEventData',
            'Ilios\CoreBundle\Tests\Fixture\LoadUserData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCohortData',
            'Ilios\CoreBundle\Tests\Fixture\LoadDisciplineData',
            'Ilios\CoreBundle\Tests\Fixture\LoadObjectiveData',
            'Ilios\CoreBundle\Tests\Fixture\LoadCourseLearningMaterialData',
            'Ilios\CoreBundle\Tests\Fixture\LoadSessionData'
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

    public function testGetCourse()
    {
        $course = $this->container
            ->get('ilioscore.dataloader.course')
            ->getOne()
        ;

        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                'get_courses',
                ['id' => $course['id']]
            )
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($course),
            json_decode($response->getContent(), true)['courses'][0]
        );
    }

    public function testGetAllCourses()
    {
        $this->createJsonRequest('GET', $this->getUrl('cget_courses'));
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize(
                $this->container
                    ->get('ilioscore.dataloader.course')
                    ->getAll()
            ),
            json_decode($response->getContent(), true)['courses']
        );
    }

    public function testPostCourse()
    {
        $data = $this->container->get('ilioscore.dataloader.course')
            ->create();
        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        unset($postData['learningMaterials']);
        unset($postData['sessions']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_courses'),
            json_encode(['course' => $postData])
        );

        $response = $this->client->getResponse();
        $headers  = [];

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $this->assertEquals(
            $data,
            json_decode($response->getContent(), true)['courses'][0],
            $response->getContent()
        );
    }

    public function testPostBadCourse()
    {
        $invalidCourse = $this->container
            ->get('ilioscore.dataloader.course')
            ->createInvalid()
        ;

        $this->createJsonRequest(
            'POST',
            $this->getUrl('post_courses'),
            json_encode(['course' => $invalidCourse])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutCourse()
    {
        $data = $this->container
            ->get('ilioscore.dataloader.course')
            ->getOne();

        $postData = $data;
        //unset any parameters which should not be POSTed
        unset($postData['id']);
        unset($postData['learningMaterials']);
        unset($postData['sessions']);

        $this->createJsonRequest(
            'PUT',
            $this->getUrl(
                'put_courses',
                ['id' => $data['id']]
            ),
            json_encode(['course' => $postData])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $this->assertEquals(
            $this->mockSerialize($data),
            json_decode($response->getContent(), true)['course']
        );
    }

    public function testDeleteCourse()
    {
        $course = $this->container
            ->get('ilioscore.dataloader.course')
            ->getOne()
        ;

        $this->client->request(
            'DELETE',
            $this->getUrl(
                'delete_courses',
                ['id' => $course['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->client->request(
            'GET',
            $this->getUrl(
                'get_courses',
                ['id' => $course['id']]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCourseNotFound()
    {
        $this->createJsonRequest(
            'GET',
            $this->getUrl('get_courses', ['id' => '0'])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }
}
