<?php

declare(strict_types=1);

namespace App\Tests\Endpoints;

use Symfony\Component\HttpFoundation\Response;
use App\Tests\DataLoader\IlmSessionData;
use App\Tests\DataLoader\OfferingData;
use App\Tests\DataLoader\SessionData;
use App\Tests\DataLoader\SessionDescriptionData;
use App\Tests\ReadWriteEndpointTest;

/**
 * Course API endpoint Test.
 * @group api_2
 */
class CourseTest extends ReadWriteEndpointTest
{
    protected $testName =  'courses';

    /**
     * @inheritdoc
     */
    protected function getFixtures()
    {
        return [
            'App\Tests\Fixture\LoadCourseData',
            'App\Tests\Fixture\LoadCourseClerkshipTypeData',
            'App\Tests\Fixture\LoadSchoolData',
            'App\Tests\Fixture\LoadUserData',
            'App\Tests\Fixture\LoadCohortData',
            'App\Tests\Fixture\LoadTermData',
            'App\Tests\Fixture\LoadCourseLearningMaterialData',
            'App\Tests\Fixture\LoadSessionData',
            'App\Tests\Fixture\LoadSessionDescriptionData',
            'App\Tests\Fixture\LoadOfferingData',
            'App\Tests\Fixture\LoadProgramYearData',
            'App\Tests\Fixture\LoadSessionLearningMaterialData',
            'App\Tests\Fixture\LoadIlmSessionData',
            'App\Tests\Fixture\LoadProgramYearObjectiveData',
            'App\Tests\Fixture\LoadCourseObjectiveData',
            'App\Tests\Fixture\LoadSessionObjectiveData',
        ];
    }

    /**
     * @inheritDoc
     */
    public function putsToTest()
    {
        return [
            'title' => ['title', $this->getFaker()->text],
            'level' => ['level', $this->getFaker()->randomDigit],
            'year' => ['year', $this->getFaker()->randomDigit],
            'startDate' => ['startDate', '2017-02-14T00:00:00+00:00'],
            'endDate' => ['endDate', '2017-02-14T00:00:00+00:00'],
            'externalId' => ['externalId', $this->getFaker()->text(5)],
            'locked' => ['locked', true],
            'archived' => ['archived', true],
            'publishedAsTbd' => ['publishedAsTbd', true],
            'published' => ['published', false],
            'clerkshipType' => ['clerkshipType', 2],
            'removeClerkshipType' => ['clerkshipType', null],
            'school' => ['school', 2],
            'directors' => ['directors', [2]],
            'removeDirectors' => ['directors', []],
            'replaceDirectors' => ['directors', [4]],
            'administrators' => ['administrators', [2]],
            'cohorts' => ['cohorts', [2]],
            'terms' => ['terms', [2]],
            'courseObjectives' => ['courseObjectives', [1]],
            'meshDescriptors' => ['meshDescriptors', ['abc3']],
            'learningMaterials' => ['learningMaterials', [1], $skipped = true],
            'sessions' => ['sessions', [1], $skipped = true],
            'ancestor' => ['ancestor', 2],
            'descendants' => ['descendants', ['3'], $skipped = true],
        ];
    }

    /**
     * @inheritDoc
     */
    public function readOnlyPropertiesToTest()
    {
        return [
            'id' => ['id', 1, 99],
        ];
    }

    /**
     * @inheritDoc
     */
    public function filtersToTest()
    {
        return [
            'id' => [[0], ['id' => 1]],
            'ids' => [[0, 2], ['id' => [1, 3]]],
            'title' => [[0], ['title' => 'firstCourse']],
            'level' => [[3, 4], ['level' => 3]],
            'year' => [[1, 2], ['year' => 2012]],
            'startDate' => [[1], ['startDate' => '2013-09-01T00:00:00+00:00'], $skipped = true],
            'endDate' => [[2], ['endDate' => '2013-12-14T00:00:00+00:00'], $skipped = true],
            'externalId' => [[2], ['externalId' => 'course3']],
            'locked' => [[4], ['locked' => true]],
            'archived' => [[4], ['archived' => true]],
            'publishedAsTbd' => [[4], ['publishedAsTbd' => true]],
            'published' => [[0, 4], ['published' => true]],
            'clerkshipType' => [[0, 1], ['clerkshipType' => 1]],
            'school' => [[2, 3, 4], ['school' => 2]],
            'schools' => [[2, 3, 4], ['schools' => [2]]],
            'directors' => [[1, 3], ['directors' => [2]], $skipped = true],
            'administrators' => [[0], ['administrators' => [1]], $skipped = true],
            'cohorts' => [[2], ['cohorts' => [2]], $skipped = true],
            'terms' => [[0, 1], ['terms' => [1]]],
            'meshDescriptors' => [[0, 1, 3], ['meshDescriptors' => ['abc1', 'abc2']]],
            'learningMaterials' => [[0, 1, 3], ['learningMaterials' => [1, 3]]],
            'sessions' => [[1], ['sessions' => [3]]],
            'ancestor' => [[3], ['ancestor' => 3]],
            'ancestors' => [[3], ['ancestors' => [3]]],
            'descendants' => [[0], ['descendants' => [1]], $skipped = true],
            'programs' => [[3, 4], ['programs' => [2]]],
            'instructors' => [[0, 1, 3], ['instructors' => [1, 2]]],
            'instructorGroups' => [[0, 1], ['instructorGroups' => [1]]],
            'programYears' => [[2], ['programYears' => [2]]],
            'competencies' => [[0], ['competencies' => [1]]],
            'yearAndLevel' => [[3, 4], ['level' => 3, 'year' => 2013]],
            'yearAndSchool' => [[3, 4], ['school' => 2, 'year' => 2013]],
        ];
    }

    public function testGetMyCourses()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $this->filterTest(
            ['my' => true],
            [$all[0], $all[1], $all[3]]
        );
    }

    public function testGetMyCoursesSorted()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $this->filterTest(
            ['my' => true, 'order_by[year]' => 'ASC', 'order_by[id]' => 'DESC'],
            [$all[1], $all[3], $all[0]]
        );
    }

    public function testGetMyCoursesFailureOnBogusOrderBy()
    {
        $this->badFilterTest(
            ['my' => true, 'order_by[glefarknik]' => 'ASC']
        );
    }

    public function testGetMyCoursesFailureOnBogusFilterBy()
    {
        $this->badFilterTest(
            ['my' => true, 'filters[farnk]' => 1]
        );
    }

    public function testGetMyCoursesWithLimit()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $this->filterTest(
            ['my' => true, 'limit' => 2],
            [$all[0], $all[1]]
        );
    }

    public function testGetMyCoursesWithLimitAndOffset()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $this->filterTest(
            ['my' => true, 'limit' => 1, 'offset' => 1],
            [$all[1]]
        );
    }

    public function testGetMyCoursesFilteredByYear()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $this->filterTest(
            ['my' => true, 'filters[year]' => '2012'],
            [$all[1]]
        );

        $this->filterTest(
            ['my' => true, 'filters[year]' => '2013'],
            [$all[3]]
        );

        $this->filterTest(
            ['my' => true, 'filters[year]' => ['2012', '2013']],
            [$all[1], $all[3]]
        );
    }

    /**
     * Ember doesn't send the non-owning side of many2one relationships
     */
    public function testPutCourseWithoutSessionsAndLms()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->getOne();

        $postData = $data;
        unset($postData['sessions']);
        unset($postData['learningMaterials']);

        $this->putTest($data, $postData, $data['id']);
    }

    public function testRolloverCourse()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();

        $newCourse = $this->rolloverCourse([
            'id' => $course['id'],
            'year' => 2019,
            'newStartDate' => 'false',
            'skipOfferings' => 'false',
        ]);

        $this->assertSame($course['title'], $newCourse['title']);
        $this->assertSame($course['level'], $newCourse['level']);
        $this->assertSame($course['externalId'], $newCourse['externalId']);
        $this->assertSame(2019, $newCourse['year']);
        $this->assertSame('2019-09-01T00:00:00+00:00', $newCourse['startDate']);
        $this->assertSame('2019-12-29T00:00:00+00:00', $newCourse['endDate']);
        $this->assertFalse($newCourse['locked']);
        $this->assertFalse($newCourse['archived']);
        $this->assertFalse($newCourse['published']);
        $this->assertFalse($newCourse['publishedAsTbd']);

        $this->assertEquals($course['clerkshipType'], $newCourse['clerkshipType']);
        $this->assertEquals($course['school'], $newCourse['school']);
        $this->assertEquals($course['directors'], $newCourse['directors']);
        $this->assertEmpty($newCourse['cohorts']);
        $this->assertEquals($course['terms'], $newCourse['terms']);
        $this->assertSame(count($course['courseObjectives']), count($newCourse['courseObjectives']));
        $this->assertEquals($course['meshDescriptors'], $newCourse['meshDescriptors']);
        $this->assertSame(count($course['learningMaterials']), count($newCourse['learningMaterials']));
        $this->assertEquals($course['id'], $newCourse['ancestor']);

        $newSessions = $newCourse['sessions'];
        $this->assertEquals(count($newSessions), 2);
        $sessions = $this->getContainer()->get(SessionData::class)->getAll();
        $lastSessionId = array_pop($sessions)['id'];

        $this->assertEquals($lastSessionId + 1, $newSessions[0], 'incremented session id 1');
        $this->assertEquals($lastSessionId + 2, $newSessions[1], 'incremented session id 2');

        $newSessionsData = $this->getFiltered('sessions', 'sessions', ['filters[id]' => $newSessions]);
        $offerings = $this->getContainer()->get(OfferingData::class)->getAll();
        $lastOfferingId = array_pop($offerings)['id'];

        $firstSessionOfferings = array_map('strval', [$lastOfferingId + 1, $lastOfferingId + 2]);
        $secondSessionOfferings = array_map('strval', [$lastOfferingId + 3, $lastOfferingId + 4, $lastOfferingId + 5]);

        $this->assertEquals($firstSessionOfferings, $newSessionsData[0]['offerings']);
        $this->assertEquals($secondSessionOfferings, $newSessionsData[1]['offerings']);

        $newDescriptionIds = array_map(function (array $session) {
            return $session['sessionDescription'];
        }, $newSessionsData);
        $this->assertEquals(count($newDescriptionIds), 2);
        $descriptions = $this->getContainer()->get(SessionDescriptionData::class)->getAll();
        $lastDescriptionId = $descriptions[1]['id'];

        $this->assertEquals($lastDescriptionId + 1, $newDescriptionIds[0], 'incremented description id 1');
        $this->assertEquals($lastDescriptionId + 2, $newDescriptionIds[1], 'incremented description id 2');

        $newDescriptionData = $this->getFiltered(
            'sessiondescriptions',
            'sessionDescriptions',
            ['filters[id]' => $newDescriptionIds]
        );

        $this->assertEquals($newDescriptionData[0]['description'], $descriptions[0]['description']);
        $this->assertEquals($newDescriptionData[1]['description'], $descriptions[1]['description']);
    }

    public function testRolloverCourseWithStartDate()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();

        $newCourse = $this->rolloverCourse([
            'id' => $course['id'],
            'year' => 2022,
            'newStartDate' => '2022-02-06'
        ]);

        $this->assertSame(2022, $newCourse['year']);
        $this->assertSame('2022-02-06T00:00:00+00:00', $newCourse['startDate'], 'start date is correct');
        $this->assertSame('2022-06-05T00:00:00+00:00', $newCourse['endDate'], 'end date is correct');

        $newSessions = $newCourse['sessions'];
        $this->assertEquals(count($newSessions), 2);

        $newSessionsData = $this->getFiltered('sessions', 'sessions', ['filters[id]' => $newSessions]);

        $session1Offerings = $newSessionsData[0]['offerings'];
        $session1OfferingData = $this->getFiltered('offerings', 'offerings', ['filters[id]' => $session1Offerings]);

        $this->assertEquals('2022-02-10T15:00:00+00:00', $session1OfferingData[0]['startDate']);
    }

    public function testRolloverCourseWithNoOfferings()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();

        $newCourse = $this->rolloverCourse([
            'id' => $course['id'],
            'year' => 2030,
            'skipOfferings' => true
        ]);

        $this->assertSame(2030, $newCourse['year']);
        $newSessions = $newCourse['sessions'];
        $this->assertEquals(count($newSessions), 2);
        $sessions = $this->getContainer()->get(SessionData::class)->getAll();
        $lastSessionId = array_pop($sessions)['id'];

        $this->assertEquals($lastSessionId + 1, $newSessions[0], 'incremented session id 1');
        $this->assertEquals($lastSessionId + 2, $newSessions[1], 'incremented session id 2');

        $data = $this->getFiltered('sessions', 'sessions', ['filters[id]' => $newSessions]);

        $this->assertEmpty($data[0]['offerings']);
        $this->assertEmpty($data[1]['offerings']);
    }

    public function testRolloverCourseWithNewTitle()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        /* KLUDGE!
         * Fix up the course's year to be last year.
         * Otherwise, rollover may bomb out with a "Courses cannot be rolled over to a new year before YYYY" error,
         * with YYYY being last year.
         * [ST 2018/01/02].
         */
        $course['year'] = intval(strftime('%Y'), 10) - 1;
        $newCourseTitle = 'New (very cool) course title';

        $newCourse = $this->rolloverCourse([
            'id' => $course['id'],
            'year' => $course['year'],
            'newCourseTitle' => $newCourseTitle
        ]);

        $this->assertSame($course['year'], $newCourse['year']);
        $this->assertSame($newCourseTitle, $newCourse['title']);
    }

    public function testFailRolloverToPassedYear()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        $course['year'] = 2001; // most definitely yesteryear
        $newCourseTitle = 'Does not matter';

        $parameters = array_merge([
            'version' => $this->apiVersion,
        ], [
            'id' => $course['id'],
            'year' => $course['year'],
            'newCourseTitle' => $newCourseTitle
        ]);

        $this->createJsonRequest(
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                "app_api_courses_rollover",
                $parameters
            ),
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Courses cannot be rolled over to a new year before', $data['detail']);
    }

    public function testRolloverIlmSessions()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $course = $all[1];

        $newCourse = $this->rolloverCourse([
            'id' => $course['id'],
            'year' => 2019,
        ]);

        $newSessionIds = $newCourse['sessions'];
        $this->assertEquals(count($newSessionIds), 5);

        $newSessionData = $this->getFiltered('sessions', 'sessions', ['filters[id]' => $newSessionIds]);

        $newSessionsWithILMs = array_filter($newSessionData, function (array $session) {
            return !empty($session['ilmSession']);
        });
        $this->assertEquals(4, count($newSessionsWithILMs));

        $newIlmIds = array_map(function (array $session) {
            return $session['ilmSession'];
        }, $newSessionsWithILMs);
        $newIlmIds = array_values($newIlmIds);

        $ilms = $this->getContainer()->get(IlmSessionData::class)->getAll();
        $lastIlmId = $ilms[key(array_slice($ilms, -1, 1, true))]['id'];

        $this->assertEquals($lastIlmId + 1, $newIlmIds[0], 'incremented ilm id 1');
        $this->assertEquals($lastIlmId + 2, $newIlmIds[1], 'incremented ilm id 2');
        $this->assertEquals($lastIlmId + 3, $newIlmIds[2], 'incremented ilm id 3');
        $this->assertEquals($lastIlmId + 4, $newIlmIds[3], 'incremented ilm id 4');

        $newIlmData = $this->getFiltered('ilmsessions', 'ilmSessions', ['filters[id]' => $newIlmIds]);

        $this->assertEquals($newIlmData[0]['hours'], $ilms[0]['hours']);
        $this->assertEquals($newIlmData[1]['hours'], $ilms[1]['hours']);
    }

    public function testRolloverCourseWithCohorts()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();

        $newCourse = $this->rolloverCourse([
            'id' => $course['id'],
            'year' => 2019,
            'newStartDate' => 'false',
            'skipOfferings' => 'true',
            'newCohorts' => [5]
        ]);

        $this->assertSame($course['title'], $newCourse['title']);
        $this->assertCount(1, $newCourse['cohorts']);
        $this->assertSame('5', $newCourse['cohorts'][0]);
        $this->assertSame(count($course['courseObjectives']), count($newCourse['courseObjectives']));
        $oldCourseObjectiveData = $this->getFiltered(
            'courseobjectives',
            'courseObjectives',
            ['filters[id]' => $course['courseObjectives']]
        );
        $newCourseObjectivesData = $this->getFiltered(
            'courseobjectives',
            'courseObjectives',
            ['filters[id]' => $newCourse['courseObjectives']]
        );

        $this->assertCount(count($oldCourseObjectiveData), $newCourseObjectivesData);
        $this->assertCount(1, $newCourseObjectivesData[0]['programYearObjectives']);
        $this->assertEquals('2', $newCourseObjectivesData[0]['programYearObjectives'][0]);
    }

    protected function rolloverCourse(array $rolloverDetails)
    {
        $parameters = array_merge([
            'version' => $this->apiVersion,
        ], $rolloverDetails);

        $this->createJsonRequest(
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                "app_api_courses_rollover",
                $parameters
            ),
            null,
            $this->getAuthenticatedUserToken($this->kernelBrowser)
        );

        $response = $this->kernelBrowser->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_CREATED);
        $data = json_decode($response->getContent(), true)['courses'];

        $this->assertArrayHasKey(0, $data);

        return $data[0];
    }

    public function testRejectUnprivilegedPostCourse()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        $userId = 3;

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'POST',
            $this->getUrl(
                $this->kernelBrowser,
                "app_api_courses_post",
                ['version' => $this->apiVersion]
            ),
            json_encode(['courses' => [$course]])
        );
    }

    public function testRejectUnprivilegedPutCourse()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        $userId = 3;
        $id = $course['id'];

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'PUT',
            $this->getUrl(
                $this->kernelBrowser,
                "app_api_courses_put",
                ['version' => $this->apiVersion, 'id' => $id]
            ),
            json_encode(['course' => $course])
        );
    }

    public function testRejectUnprivilegedPutCourseWithWrongId()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        $userId = 3;
        $id = $course['id'];


        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'PUT',
            $this->getUrl(
                $this->kernelBrowser,
                "app_api_courses_put",
                ['version' => $this->apiVersion, 'id' => $id * 10000]
            ),
            json_encode(['course' => $course])
        );
    }

    public function testRejectUnprivilegedDeleteCourse()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        $userId = 3;
        $id = $course['id'];

        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'DELETE',
            $this->getUrl(
                $this->kernelBrowser,
                "app_api_courses_delete",
                ['version' => $this->apiVersion, 'id' => $id]
            )
        );
    }

    public function testRejectUnprivilegedRollover()
    {
        $dataLoader = $this->getDataLoader();
        $course = $dataLoader->getOne();
        $userId = 3;
        $id = $course['id'];

        $rolloverData = [
            'version' => $this->apiVersion,
            'id' => $id,
            'year' => 2019,
            'newStartDate' => 'false',
            'skipOfferings' => 'false',
        ];
        $this->canNot(
            $this->kernelBrowser,
            $userId,
            'POST',
            $this->getUrl($this->kernelBrowser, "app_api_courses_rollover", $rolloverData)
        );
    }

    public function testCourseCanBeUnlocked()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->getOne();
        $data['locked'] = true;
        $postData = $data;
        $responseData = $this->putTest($data, $postData, $data['id']);
        $this->assertTrue(
            $responseData['locked']
        );

        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->getOne();
        $data['locked'] = false;
        $postData = $data;
        $responseData = $this->putTest($data, $postData, $data['id']);
        $this->assertFalse(
            $responseData['locked']
        );
    }

    public function testRemovingCourseObjectiveRemovesSessionObjectivesToo()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->getOne();
        $courseObjectiveId = $data['courseObjectives'][0];
        $courseObjective = $this->getOne('courseobjectives', 'courseObjectives', $courseObjectiveId);
        $sessionObjectiveId = $courseObjective['sessionObjectives'][0];
        $sessionObjective = $this->getOne('sessionobjectives', 'sessionObjectives', $sessionObjectiveId);
        // session objective is linked to course objective
        $this->assertTrue(in_array($courseObjective['id'], $sessionObjective['courseObjectives']));

        // remove course objective
        $data['courseObjectives'] = [];
        $postData = $data;
        $this->putOne('courses', 'course', $postData['id'], $postData);

        // verify that session objective is no longer linked to removed course objective
        $sessionObjective = $this->getOne('sessionobjectives', 'sessionObjectives', $sessionObjectiveId);
        $this->assertFalse(in_array($courseObjective['id'], $sessionObjective['courseObjectives']));
    }

    public function testPutCourseWithBadSchoolId()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->create();
        $data['school'] = 99;

        $this->badPostTest($data);
    }

    public function testPutCourseWithBadSessionId()
    {
        $dataLoader = $this->getDataLoader();
        $data = $dataLoader->create();
        $data['sessions'] = [1, 99, 14];

        $this->badPostTest($data);
    }

    public function testGetMyCoursesIncludesAdministeredCourses()
    {
        $dataLoader = $this->getDataLoader();
        $all = $dataLoader->getAll();
        $this->filterTest(
            ['my' => true],
            [$all[0], $all[2], $all[4]],
            4
        );
    }

// @todo re-implement or remove [ST 2020/07/01]

//    public function testRemoveLinksFromOrphanedObjectives()
//    {
//        $dataLoader = $this->getDataLoader();
//        $data = $dataLoader->getOne();
//        $id = $data['id'];
//        $self = $this;
//
//        //create data we can depend on
//        $dataLoader = $this->getContainer()->get(ObjectiveData::class);
//        $create = [];
//        for ($i = 0; $i < 2; $i++) {
//            $arr = $dataLoader->create();
//            $arr['parents'] = ['1'];
//            $arr['children'] = ['7', '8'];
//            $arr['competency'] = 1;
//            $arr['programYearObjectives'] = [];
//            $arr['courseObjectives'] = [];
//            $arr['sessionObjectives'] = [];
//            unset($arr['id']);
//            $create[] = $arr;
//        }
//        $newObjectives = $this->postMany('objectives', 'objectives', $create);
//        $dataLoader = $this->getContainer()->get(CourseObjectiveData::class);
//        $create = [];
//        foreach ($newObjectives as $objective) {
//            $arr = $dataLoader->create();
//            unset($arr['id']);
//            $arr['course'] = $id;
//            $arr['objective'] = $objective['id'];
//            $create[] = $arr;
//        }
//        $this->postMany('courseobjectives', 'courseObjectives', $create);
//
//        $getObjectives = function ($id) use ($self) {
//            return $self->getOne('objectives', 'objectives', $id);
//        };
//        $objectives = array_map($getObjectives, array_column($newObjectives, 'id'));
//        foreach ($objectives as $arr) {
//            $this->assertNotEmpty($arr['parents'], 'parents have been created');
//            $this->assertNotEmpty($arr['children'], 'children have been created');
//            $this->assertArrayHasKey('competency', $arr);
//        }
//        $this->deleteTest($id);
//        $objectives = array_map($getObjectives, array_column($newObjectives, 'id'));
//        foreach ($objectives as $arr) {
//            $this->assertEmpty($arr['parents'], 'parents have been removed');
//            $this->assertEmpty($arr['children'], 'children have been removed');
//            $this->assertArrayNotHasKey('competency', $arr);
//        }
//    }

    public function testIncludeBothProgramYearProgramAndObjectivesWithCohort()
    {
        $includes = $this->getJsonApiIncludes(
            'courses',
            '1',
            'cohorts.programYear.program,cohorts.programYear.programYearObjectives'
        );

        $this->assertArrayHasKey('programYears', $includes);
        $this->assertArrayHasKey('programs', $includes);
        $this->assertArrayHasKey('programYearObjectives', $includes);

        $this->assertIsArray($includes['programYears']);
        $this->assertEquals(['1'], $includes['programYears']);
        $this->assertIsArray($includes['programs']);
        $this->assertEquals(['1'], $includes['programs']);
        $this->assertIsArray($includes['programYearObjectives']);
        $this->assertEquals(['1'], $includes['programYearObjectives']);
    }

    public function testIncludeSessionDetails()
    {
        $sessionRelationships = [
            'learningMaterials.learningMaterial.owningUser',
            'sessionObjectives.courseObjectives',
            'sessionObjectives.meshDescriptors',
            'sessionObjectives.terms.vocabulary',
            'offerings.learners',
            'offerings.instructors',
            'offerings.instructorGroups.users',
            'offerings.learnerGroups.users',
            'ilmSession.learners',
            'ilmSession.instructors',
            'ilmSession.instructorGroups.users',
            'ilmSession.learnerGroups.users',
            'sessionDescription',
            'terms.vocabulary',
            'meshDescriptors.trees',
        ];
        $sessionIncludes = array_reduce($sessionRelationships, function ($carry, $item) {
            return "${carry}sessions.${item},";
        }, '');

        $includes = $this->getJsonApiIncludes(
            'courses',
            '1',
            $sessionIncludes
        );

        $this->assertArrayHasKey('sessions', $includes);
        $this->assertArrayHasKey('terms', $includes);
        $this->assertArrayHasKey('vocabularies', $includes);
        $this->assertArrayHasKey('sessionObjectives', $includes);
        $this->assertArrayHasKey('courseObjectives', $includes);
        $this->assertArrayHasKey('meshDescriptors', $includes);
        $this->assertArrayHasKey('sessionDescriptions', $includes);
        $this->assertArrayHasKey('sessionLearningMaterials', $includes);
        $this->assertArrayHasKey('learningMaterials', $includes);
        $this->assertArrayHasKey('users', $includes);
        $this->assertArrayHasKey('offerings', $includes);
        $this->assertArrayHasKey('learnerGroups', $includes);
        $this->assertArrayHasKey('instructorGroups', $includes);

        $this->assertIsArray($includes['sessions']);
        $this->assertEquals(['1', '2'], $includes['sessions']);
        $this->assertIsArray($includes['terms']);
        $this->assertEquals(['1', '2', '3', '4', '5'], $includes['terms']);
        $this->assertIsArray($includes['vocabularies']);
        $this->assertEquals(['1', '2'], $includes['vocabularies']);
        $this->assertIsArray($includes['sessionObjectives']);
        $this->assertEquals(['1'], $includes['sessionObjectives']);
        $this->assertIsArray($includes['courseObjectives']);
        $this->assertEquals(['1'], $includes['courseObjectives']);
        $this->assertIsArray($includes['meshDescriptors']);
        $this->assertEquals(['abc1', 'abc2'], $includes['meshDescriptors']);
        $this->assertIsArray($includes['sessionDescriptions']);
        $this->assertEquals(['1', '2'], $includes['sessionDescriptions']);
        $this->assertIsArray($includes['sessionLearningMaterials']);
        $this->assertEquals(['1'], $includes['sessionLearningMaterials']);
        $this->assertIsArray($includes['learningMaterials']);
        $this->assertEquals(['1'], $includes['learningMaterials']);
        $this->assertIsArray($includes['users']);
        $this->assertEquals(['1', '2', '4', '5'], $includes['users']);
        $this->assertIsArray($includes['offerings']);
        $this->assertEquals(['1', '2', '3', '4', '5'], $includes['offerings']);
        $this->assertIsArray($includes['learnerGroups']);
        $this->assertEquals(['1', '2', '5'], $includes['learnerGroups']);
        $this->assertIsArray($includes['instructorGroups']);
        $this->assertEquals(['1', '2'], $includes['instructorGroups']);
    }
}
