<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use App\Tests\DataLoader\ProgramYearData;
use App\Tests\DataLoader\ProgramYearObjectiveData;
use App\Tests\DataLoader\TermData;
use App\Tests\ReadWriteEndpointTest;
use Symfony\Component\HttpFoundation\Response;

/**
 * ProgramYearObjectiveTest API endpoint Test.
 * @group api_2
 */
class ProgramYearObjectiveTest extends ReadWriteEndpointTest
{
    protected $testName =  'programYearObjectives';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadMeshDescriptorData',
            'App\Tests\Fixture\LoadTermData',
            'App\Tests\Fixture\LoadCourseData',
            'App\Tests\Fixture\LoadSessionData',
            'App\Tests\Fixture\LoadSessionObjectiveData',
            'App\Tests\Fixture\LoadProgramYearData',
            'App\Tests\Fixture\LoadCourseObjectiveData',
            'App\Tests\Fixture\LoadProgramYearObjectiveData',
        ];
    }

    /**
     * @inheritdoc
     */
    public function putsToTest()
    {
        return [
            'title' => ['title', $this->getFaker()->text],
            'position' => ['position', $this->getFaker()->randomDigit],
            'notActive' => ['active', false],
            'programYear' => ['programYear', 2],
            'terms' => ['terms', [1, 4]],
            'meshDescriptors' => ['meshDescriptors', ['abc2']],
            'competency' => ['competency', 2],
            'courseObjectives' => ['courseObjectives', [2]]

        ];
    }

    /**
     * @inheritdoc
     */
    public function readOnlyPropertiesToTest()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function filtersToTest()
    {
        return [
            'id' => [[0], ['id' => 1]],
            'ids' => [[0, 1], ['id' => [1, 2]]],
            'programYear' => [[0], ['programYear' => 1]],
            'terms' => [[0, 1], ['terms' => [2]]],
            'position' => [[0, 1], ['position' => 0]],
            'title' => [[1], ['title' => 'program year objective 2']],
            'active' => [[0, 1], ['active' => 1]],
            'notActive' => [[], ['active' => 0]],
            'ancestor' => [[1], ['ancestor' => 1]],
            'competencies' => [[1], ['competency' => 2]],
        ];
    }

    protected function createMany(int $count): array
    {
        $programYearDataLoader = $this->getContainer()->get(ProgramYearData::class);
        $programYears = $programYearDataLoader->createMany($count);
        $savedProgramYears = $this->postMany('programyears', 'programYears', $programYears);

        $dataLoader = $this->getDataLoader();

        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $arr = $dataLoader->create();
            $arr['id'] += $i;
            $arr['programYear'] = $savedProgramYears[$i]['id'];
            $arr['title'] = 'Program Year Objective ' . $arr['id'];
            $data[] = $arr;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function testPostMany()
    {
        $data = $this->createMany(10);
        $this->postManyTest($data);
    }

    public function testPostManyJsonApi()
    {
        $data = $this->createMany(10);
        $jsonApiData = $this->getDataLoader()->createBulkJsonApi($data);
        $this->postManyJsonApiTest($jsonApiData, $data);
    }

    /**
     * @inheritdoc
     */
    public function testPutForAllData()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();

        $n = count($all);
        $termsDataLoader = $this->getContainer()->get(TermData::class);
        $terms = $termsDataLoader->createMany($n);
        $savedTerms = $this->postMany('terms', 'terms', $terms);

        for ($i = 0; $i < $n; $i++) {
            $data = $all[$i];
            $data['terms'][] = $savedTerms[$i]['id'];
            $this->putTest($data, $data, $data['id']);
        }
    }

    public function testRemoveLinksFromOrphanedObjectives()
    {
        // @todo re-implement or remove this. [ST 2020/06/22]
        $this->markTestSkipped('tbd');
//        $dataLoader = $this->getContainer()->get(ObjectiveData::class);
//        $arr = $dataLoader->create();
//        $arr['parents'] = ['1'];
//        $arr['children'] = ['7', '8'];
//        $arr['competency'] = 1;
//        $arr['programYearObjectives'] = [];
//        $arr['courseObjectives'] = [];
//        $arr['sessionObjectives'] = [];
//        unset($arr['id']);
//        $objective = $this->postOne('objectives', 'objective', 'objectives', $arr);
//        $dataLoader = $this->getContainer()->get(ProgramYearData::class);
//        $arr = $dataLoader->create();
//        $programYear = $this->postOne('programyears', 'programYear', 'programYears', $arr);
//
//        $dataLoader = $this->getDataLoader();
//        $arr = $dataLoader->create();
//        $arr['programYear'] = $programYear['id'];
//        $arr['objective'] = $objective['id'];
//        unset($arr['id']);
//        $programYearObjective = $this->postOne(
//            'programyearobjectives',
//            'programYearObjective',
//            'programYearObjectives',
//            $arr
//        );
//
//        $this->assertNotEmpty($objective['parents'], 'parents have been created');
//        $this->assertNotEmpty($objective['children'], 'children have been created');
//        $this->assertArrayHasKey('competency', $objective);
//
//        $this->deleteTest($programYearObjective['id']);
//
//        $objective = $this->getOne('objectives', 'objectives', $objective['id']);
//
//        $this->assertEmpty($objective['parents'], 'parents have been removed');
//        $this->assertEmpty($objective['children'], 'children have been removed');
//        $this->assertArrayNotHasKey('competency', $objective);
    }

    /**
     * @dataProvider inputSanitationTestProvider
     *
     * @param string $input A given objective title as un-sanitized input.
     * @param string $output The expected sanitized objective title output as returned from the server.
     *
     */
    public function testInputSanitation($input, $output)
    {
        $postData = $this->getContainer()->get(ProgramYearObjectiveData::class)
            ->create();
        $postData['title'] = $input;
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl($this->kernelBrowser, 'app_api_programyearobjectives_post', [
                'version' => $this->apiVersion
            ]),
            json_encode(['programYearObjectives' => [$postData]]),
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();

        $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $this->assertEquals(
            json_decode($response->getContent(), true)['programYearObjectives'][0]['title'],
            $output,
            $response->getContent()
        );
    }

    /**
     * @return array
     */
    public function inputSanitationTestProvider()
    {
        return [
            ['foo', 'foo'],
            ['<p>foo</p>', '<p>foo</p>'],
            ['<ul><li>foo</li></ul>', '<ul><li>foo</li></ul>'],
            ['<script>alert("hello");</script><p>foo</p>', '<p>foo</p>'],
            [
                '<a href="https://iliosproject.org" target="_blank">Ilios</a>',
                '<a href="https://iliosproject.org" target="_blank" rel="noreferrer noopener">Ilios</a>'
            ],
        ];
    }

    /**
     * Assert that a POST request fails if form validation fails due to input sanitation.
     */
    public function testInputSanitationFailure()
    {
        $postData = $this->getContainer()->get(ProgramYearObjectiveData::class)
            ->create();
        // this markup will get stripped out, leaving a blank string as input.
        // which in turn will cause the form validation to fail.
        $postData['title'] = '<iframe></iframe>';
        unset($postData['id']);

        $this->createJsonRequest(
            'POST',
            $this->getUrl($this->kernelBrowser, 'app_api_programyearobjectives_post', [
                'version' => $this->apiVersion
            ]),
            json_encode(['programYearObjectives' => [$postData]]),
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);
    }
}
