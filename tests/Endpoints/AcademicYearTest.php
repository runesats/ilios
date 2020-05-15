<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use Symfony\Component\HttpFoundation\Response;
use App\Tests\DataLoader\CourseData;
use App\Tests\ReadEndpointTest;

/**
 * AamcMethod API endpoint Test.
 * @group api_5
 */
class AcademicYearTest extends ReadEndpointTest
{
    protected $testName = 'academicYears';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadCourseData',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest()
    {
        return [
            'id' => [[1], ['id' => 2013], $skipped = true],
            'ids' => [[0, 2], ['id' => [2012, 2016]], $skipped = true],
            'title' => [[1], ['id' => 2013], $skipped = true],
            'titles' => [[0, 2], ['id' => [2012, 2016]], $skipped = true],
        ];
    }


    public function testGetOne()
    {
        $academicYears = $this->getYears();
        $data = $academicYears[0];
        $returnedData = $this->getOne('academicyears', 'academicYears', $data['id']);
        $this->compareData($data, $returnedData);
    }

    public function testGetAll()
    {
        $endpoint = $this->getPluralName();
        $responseKey = $this->getCamelCasedPluralName();
        $academicYears = $this->getYears();
        $this->createJsonRequest(
            'GET',
            $this->getUrl(
                $this->kernelBrowser,
                'ilios_api_getall',
                ['version' => $this->apiVersion, 'object' => $endpoint]
            ),
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );
        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_OK);
        $responses = json_decode($response->getContent(), true)[$responseKey];


        $this->assertEquals(
            $academicYears,
            $responses
        );
    }

    public function testPostIs404()
    {
        $this->fourOhFourTest('POST');
    }

    public function testPutIs404()
    {
        $academicYears = $this->getYears();
        $id = $academicYears[0]['id'];

        $this->fourOhFourTest('PUT', ['id' => $id]);
    }

    public function testDeleteIs404()
    {
        $academicYears = $this->getYears();
        $id = $academicYears[0]['id'];

        $this->fourOhFourTest('DELETE', ['id' => $id]);
    }

    protected function fourOhFourTest($type, array $parameters = [])
    {
        $parameters = array_merge(
            ['version' => $this->apiVersion, 'object' => 'academicyears'],
            $parameters
        );

        $url = $this->getUrl(
            $this->kernelBrowser,
            'ilios_api_academicyear_404',
            $parameters
        );
        $this->createJsonRequest(
            $type,
            $url,
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
    }

    protected function getYears()
    {
        $loader = $this->getContainer()->get(CourseData::class);
        $data = $loader->getAll();
        $academicYears = array_map(function ($arr) {
            return $arr['year'];
        }, $data);
        $academicYears = array_unique($academicYears);
        sort($academicYears);
        $academicYears = array_map(function ($year) {
            return [
                'id' => $year,
                'title' => $year
            ];
        }, $academicYears);

        return $academicYears;
    }
}
