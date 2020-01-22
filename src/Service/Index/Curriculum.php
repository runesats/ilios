<?php

declare(strict_types=1);

namespace App\Service\Index;

use App\Classes\ElasticSearchBase;
use App\Classes\IndexableCourse;
use Exception;

class Curriculum extends ElasticSearchBase
{

    public const INDEX = 'ilios-curriculum';
    public const SESSION_ID_PREFIX = 'session_';

    /**
     * @param string $query
     * @param bool $onlySuggest should the search return only suggestions
     * @return array
     * @throws Exception when search is not configured
     */
    public function search(string $query, $onlySuggest)
    {
        if (!$this->enabled) {
            throw new Exception("Search is not configured, isEnabled() should be called before calling this method");
        }

        $suggestFields = [
            'courseTitle',
            'courseTerms',
            'courseMeshDescriptorIds',
            'courseMeshDescriptorNames',
            'courseLearningMaterialTitles',
            'sessionTitle',
            'sessionType',
            'sessionTerms',
            'sessionMeshDescriptorIds',
            'sessionMeshDescriptorNames',
            'sessionLearningMaterialTitles',
        ];
        $suggest = array_reduce($suggestFields, function ($carry, $field) use ($query) {
            $carry[$field] = [
                'prefix' => $query,
                'completion' => [
                    'field' => "${field}.cmp",
                    'skip_duplicates' => true,
                ]
            ];

            return $carry;
        }, []);

        $params = [
            'type' => '_doc',
            'index' => self::INDEX,
            'body' => [
                'suggest' => $suggest,
                "_source" => [
                    'courseId',
                    'courseTitle',
                    'courseYear',
                    'sessionId',
                    'sessionTitle',
                    'school',
                ],
                'sort' => '_score',
                'size' => 1000
            ]
        ];

        if (!$onlySuggest) {
            $params['body']['query'] = $this->buildCurriculumSearch($query);
        }

        $results = $this->doSearch($params);

        return $this->parseCurriculumSearchResults($results);
    }

    /**
     * @param IndexableCourse[] $courses
     * @return bool
     */
    public function index(array $courses): bool
    {
        foreach ($courses as $course) {
            if (!$course instanceof IndexableCourse) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '$courses must be an array of %s. %s found',
                        IndexableCourse::class,
                        get_class($course)
                    )
                );
            }
        }

        $input = array_reduce($courses, function (array $carry, IndexableCourse $item) {
            $sessions = $item->createIndexObjects();
            $sessionsWithMaterials = $this->attachLearningMaterialsToSession($sessions);
            return array_merge($carry, $sessionsWithMaterials);
        }, []);

        $result = $this->doBulkIndex(self::INDEX, $input);

        if ($result['errors']) {
            $errors = array_map(function (array $item) {
                if (array_key_exists('error', $item['index'])) {
                    return $item['index']['error']['reason'];
                }
            }, $result['items']);
            $clean = array_filter($errors);
            $str = join(';', array_unique($clean));
            $count = count($clean);
            throw new Exception("Failed to index all courses ${count} errors. Error text: ${str}");
        }

        return true;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteCourse(int $id): bool
    {
        $result = $this->doDeleteByQuery([
            'index' => self::INDEX,
            'body' => [
                'query' => [
                    'term' => ['courseId' => $id]
                ]
            ]
        ]);

        return !count($result['failures']);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteSession(int $id): bool
    {
        $result = $this->doDelete([
            'index' => self::INDEX,
            'id' => self::SESSION_ID_PREFIX . $id
        ]);

        return $result['result'] === 'deleted';
    }

    protected function attachLearningMaterialsToSession(array $sessions): array
    {
        $courseIds = array_column($sessions, 'courseFileLearningMaterialIds');
        $sessionIds = array_column($sessions, 'sessionFileLearningMaterialIds');
        $learningMaterialIds = array_values(array_unique(array_merge([], ...$courseIds, ...$sessionIds)));
        $materialsById = [];
        if (!empty($learningMaterialIds)) {
            $params = [
                'type' => '_doc',
                'index' => LearningMaterials::INDEX,
                'body' => [
                    'query' => [
                        'terms' => [
                            'learningMaterialId' => $learningMaterialIds
                        ]
                    ],
                    "_source" => [
                        'learningMaterialId',
                        'material.content',
                    ]
                ]
            ];
            $results = $this->client->search($params);

            $materialsById = array_reduce($results['hits']['hits'], function (array $carry, array $hit) {
                $result = $hit['_source'];
                $id = $result['learningMaterialId'];

                if (array_key_exists('material', $result)) {
                    $carry[$id][] = $result['material']['content'];
                }

                return $carry;
            }, []);
        }

        return array_map(function (array $session) use ($materialsById) {
            foreach ($session['sessionFileLearningMaterialIds'] as $id) {
                if (array_key_exists($id, $materialsById)) {
                    foreach ($materialsById[$id] as $value) {
                        $session['sessionLearningMaterialAttachments'][] = $value;
                    }
                }
            }
            unset($session['sessionFileLearningMaterialIds']);
            foreach ($session['courseFileLearningMaterialIds'] as $id) {
                if (array_key_exists($id, $materialsById)) {
                    foreach ($materialsById[$id] as $value) {
                        $session['courseLearningMaterialAttachments'][] = $value;
                    }
                }
            }
            unset($session['courseFileLearningMaterialIds']);

            return $session;
        }, $sessions);
    }

    /**
     * Construct the query to search the curriculum
     * @param string $query
     * @return array
     */
    protected function buildCurriculumSearch(string $query): array
    {
        $mustFields = [
            'courseId',
            'courseYear',
            'courseTitle',
            'courseTitle.ngram',
            'courseTerms',
            'courseTerms.ngram',
            'courseObjectives',
            'courseObjectives.ngram',
            'courseLearningMaterialTitles',
            'courseLearningMaterialTitles.ngram',
            'courseLearningMaterialDescriptions',
            'courseLearningMaterialDescriptions.ngram',
            'courseLearningMaterialCitation',
            'courseLearningMaterialCitation.ngram',
            'courseMeshDescriptorIds',
            'courseMeshDescriptorNames',
            'courseMeshDescriptorNames.ngram',
            'courseMeshDescriptorAnnotations',
            'courseMeshDescriptorAnnotations.ngram',
            'courseLearningMaterialAttachments',
            'courseLearningMaterialAttachments.ngram',
            'sessionId',
            'sessionTitle',
            'sessionTitle.ngram',
            'sessionDescription',
            'sessionDescription.ngram',
            'sessionType',
            'sessionTerms',
            'sessionTerms.ngram',
            'sessionObjectives',
            'sessionObjectives.ngram',
            'sessionLearningMaterialTitles',
            'sessionLearningMaterialTitles.ngram',
            'sessionLearningMaterialDescriptions',
            'sessionLearningMaterialDescriptions.ngram',
            'sessionLearningMaterialCitation',
            'sessionLearningMaterialCitation.ngram',
            'sessionMeshDescriptorIds',
            'sessionMeshDescriptorNames',
            'sessionMeshDescriptorNames.ngram',
            'sessionMeshDescriptorAnnotations',
            'sessionMeshDescriptorAnnotations.ngram',
            'sessionLearningMaterialAttachments',
            'sessionLearningMaterialAttachments.ngram',
        ];

        $shouldFields = [
            'courseTitle',
            'courseTerms',
            'courseObjectives',
            'courseLearningMaterialTitles',
            'courseLearningMaterialDescriptions',
            'courseLearningMaterialAttachments',
            'sessionTitle',
            'sessionDescription',
            'sessionType',
            'sessionTerms',
            'sessionObjectives',
            'sessionLearningMaterialTitles',
            'sessionLearningMaterialDescriptions',
            'sessionLearningMaterialAttachments',
        ];

        $mustMatch = array_map(function ($field) use ($query) {
            return [ 'match' => [ $field => [
                'query' => $query,
                '_name' => $field,
            ] ] ];
        }, $mustFields);

        /**
         * At least one of the mustMatch queries has to be a match
         * but we wrap it in a should block so they don't all have to match
         */
        $must = ['bool' => [
            'should' => $mustMatch
        ]];

        /**
         * The should queries are designed to boost the total score of
         * results that match more closely than the MUST set above so when
         * users enter a complete word like move it will score higher than
         * than a partial match on movement
         */
        $should = array_reduce(
            $shouldFields,
            function (array $carry, string $field) use ($query) {
                $matches = array_map(function (string $type) use ($field, $query) {
                    $fullField = "${field}.${type}";
                    return [ 'match' => [ $fullField => ['query' => $query, '_name' => $fullField] ] ];
                }, ['english', 'raw']);

                return array_merge($carry, $matches);
            },
            []
        );

        return [
            'bool' => [
                'must' => $must,
                'should' => $should,
            ]
        ];
    }

    protected function parseCurriculumSearchResults(array $results): array
    {
        $autocompleteSuggestions = array_reduce(
            $results['suggest'],
            function (array $carry, array $item) {
                $options = array_map(function (array $arr) {
                    return $arr['text'];
                }, $item[0]['options']);

                return array_unique(array_merge($carry, $options));
            },
            []
        );

        $mappedResults = array_map(function (array $arr) {
            $courseMatches = array_filter($arr['matched_queries'], function (string $match) {
                return strpos($match, 'course') === 0;
            });
            $sessionMatches = array_filter($arr['matched_queries'], function (string $match) {
                return strpos($match, 'session') === 0;
            });
            $rhett = $arr['_source'];
            $rhett['score'] = $arr['_score'];
            $rhett['courseMatches'] = $courseMatches;
            $rhett['sessionMatches'] = $sessionMatches;

            return $rhett;
        }, $results['hits']['hits']);

        $courses = array_reduce($mappedResults, function (array $carry, array $item) {
            $id = $item['courseId'];
            if (!array_key_exists($id, $carry)) {
                $carry[$id] = [
                    'id' => $id,
                    'title' => $item['courseTitle'],
                    'year' => $item['courseYear'],
                    'school' => $item['school'],
                    'bestScore' => 0,
                    'sessions' => [],
                    'matchedIn' => [],
                ];
            }
            $courseMatches = array_map(function (string $match) {
                $split = explode('.', $match);
                $field = strtolower(substr($split[0], strlen('course')));
                if (strpos($field, 'meshdescriptor') !== false) {
                    $field = 'meshdescriptors';
                }
                if (strpos($field, 'learningmaterial') !== false) {
                    $field = 'learningmaterials';
                }

                return $field;
            }, $item['courseMatches']);
            $sessionMatches = array_map(function (string $match) {
                $split = explode('.', $match);
                $field = strtolower(substr($split[0], strlen('session')));
                if (strpos($field, 'meshdescriptor') !== false) {
                    $field = 'meshdescriptors';
                }
                if (strpos($field, 'learningmaterial') !== false) {
                    $field = 'learningmaterials';
                }

                return $field;
            }, $item['sessionMatches']);
            $carry[$id]['matchedIn'] = array_values(array_unique(
                array_merge($courseMatches, $carry[$id]['matchedIn'])
            ));
            if ($item['score'] > $carry[$id]['bestScore']) {
                $carry[$id]['bestScore'] = $item['score'];
            }
            $carry[$id]['sessions'][] = [
                'id' => $item['sessionId'],
                'title' => $item['sessionTitle'],
                'score' => $item['score'],
                'matchedIn' => array_values(array_unique($sessionMatches)),
            ];

            return $carry;
        }, []);

        usort($courses, function ($a, $b) {
            return $b['bestScore'] <=> $a['bestScore'];
        });

        return [
            'autocomplete' => $autocompleteSuggestions,
            'courses' => $courses
        ];
    }
}
