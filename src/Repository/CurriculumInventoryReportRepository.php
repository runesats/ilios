<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CourseObjective;
use App\Entity\CurriculumInventoryReport;
use App\Entity\Manager\ManagerInterface;
use App\Entity\ProgramYearObjective;
use App\Entity\Session;
use App\Entity\SessionObjective;
use App\Traits\ManagerRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use App\Entity\DTO\CurriculumInventoryReportDTO;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\CurriculumInventoryReportInterface;

class CurriculumInventoryReportRepository extends ServiceEntityRepository implements
    DTORepositoryInterface,
    ManagerInterface
{
    use ManagerRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurriculumInventoryReport::class);
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT x')->from(CurriculumInventoryReport::class, 'x');

        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find and hydrate as DTOs
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     *
     * @return array
     */
    public function findDTOsBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->_em->createQueryBuilder()->select('x')
            ->distinct()->from(CurriculumInventoryReport::class, 'x');
        $this->attachCriteriaToQueryBuilder($qb, $criteria, $orderBy, $limit, $offset);

        /** @var CurriculumInventoryReportDTO[] $curriculumInventoryReportDTOs */
        $curriculumInventoryReportDTOs = [];
        foreach ($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY) as $arr) {
            $curriculumInventoryReportDTOs[$arr['id']] = new CurriculumInventoryReportDTO(
                $arr['id'],
                $arr['name'],
                $arr['description'],
                $arr['year'],
                $arr['startDate'],
                $arr['endDate'],
                $arr['token']
            );
        }
        $curriculumInventoryReportIds = array_keys($curriculumInventoryReportDTOs);

        $qb = $this->_em->createQueryBuilder()
            ->select(
                'x.id as xId, ' .
                'export.id AS exportId, sequence.id AS sequenceId, program.id AS programId, ' .
                'school.id AS schoolId'
            )
            ->from(CurriculumInventoryReport::class, 'x')
            ->join('x.program', 'program')
            ->join('program.school', 'school')
            ->leftJoin('x.sequence', 'sequence')
            ->leftJoin('x.export', 'export')
            ->where($qb->expr()->in('x.id', ':ids'))
            ->setParameter('ids', $curriculumInventoryReportIds);

        foreach ($qb->getQuery()->getResult() as $arr) {
            $curriculumInventoryReportDTOs[$arr['xId']]->export = $arr['exportId'] ? (int)$arr['exportId'] : null;
            $curriculumInventoryReportDTOs[$arr['xId']]->sequence = $arr['sequenceId'] ? (int)$arr['sequenceId'] : null;
            $curriculumInventoryReportDTOs[$arr['xId']]->program = $arr['programId'] ? (int)$arr['programId'] : null;
            $curriculumInventoryReportDTOs[$arr['xId']]->school = $arr['schoolId'] ? (int)$arr['schoolId'] : null;
        }

        $related = [
            'sequenceBlocks',
            'academicLevels',
            'administrators',
        ];
        foreach ($related as $rel) {
            $qb = $this->_em->createQueryBuilder()
                ->select('r.id AS relId, x.id AS curriculumInventoryReportId')
                ->from(CurriculumInventoryReport::class, 'x')
                ->join("x.{$rel}", 'r')
                ->where($qb->expr()->in('x.id', ':ids'))
                ->orderBy('relId')
                ->setParameter('ids', $curriculumInventoryReportIds);
            foreach ($qb->getQuery()->getResult() as $arr) {
                $curriculumInventoryReportDTOs[$arr['curriculumInventoryReportId']]->{$rel}[] = $arr['relId'];
            }
        }
        return array_values($curriculumInventoryReportDTOs);
    }


    /**
     * @param QueryBuilder $qb
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     *
     * @return QueryBuilder
     */
    protected function attachCriteriaToQueryBuilder(QueryBuilder $qb, $criteria, $orderBy, $limit, $offset)
    {
        if (array_key_exists('sequenceBlocks', $criteria)) {
            $ids = is_array($criteria['sequenceBlocks']) ? $criteria['sequenceBlocks'] : [$criteria['sequenceBlocks']];
            $qb->join('x.sequenceBlocks', 'sb');
            $qb->andWhere($qb->expr()->in('sb.id', ':sequenceBlocks'));
            $qb->setParameter(':sequenceBlocks', $ids);
        }
        if (array_key_exists('academicLevels', $criteria)) {
            $ids = is_array($criteria['academicLevels']) ? $criteria['academicLevels'] : [$criteria['academicLevels']];
            $qb->join('x.academicLevels', 'al');
            $qb->andWhere($qb->expr()->in('al.id', ':academicLevels'));
            $qb->setParameter(':academicLevels', $ids);
        }

        //cleanup all the possible relationship filters
        unset($criteria['sequenceBlocks']);
        unset($criteria['academicLevels']);

        if (count($criteria)) {
            foreach ($criteria as $key => $value) {
                $values = is_array($value) ? $value : [$value];
                $qb->andWhere($qb->expr()->in("x.{$key}", ":{$key}"));
                $qb->setParameter(":{$key}", $values);
            }
        }

        if (empty($orderBy)) {
            $orderBy = ['id' => 'ASC'];
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                $qb->addOrderBy('x.' . $sort, $order);
            }
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Retrieves AAMC resource types associated with given events (sessions) in a given curriculum inventory report.
     * @param CurriculumInventoryReportInterface $report
     * @param array|int[] $eventIds
     * @return array
     */
    public function getEventResourceTypes(CurriculumInventoryReportInterface $report, array $eventIds = [])
    {
        if (empty($eventIds)) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('s.id AS event_id, art.id AS resource_type_id, art.title AS resource_type_title')
            ->distinct()
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->join('s.terms', 't')
            ->join('t.aamcResourceTypes', 'art')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->andWhere($qb->expr()->in('s.id', ':eventIds'))
            ->setParameter('id', $report->getId())
            ->setParameter('eventIds', $eventIds);

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Retrieves keywords (MeSH descriptors) associated with events (sessions)
     * in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array|int[] $eventIds
     * @return array
     */
    public function getEventKeywords(CurriculumInventoryReportInterface $report, array $eventIds = [])
    {
        $rhett = [];

        if (empty($eventIds)) {
            return $rhett;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select("s.id AS event_id, md.id, 'MeSH' AS source, md.name")
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->join('s.meshDescriptors', 'md')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->andWhere($qb->expr()->in('s.id', ':eventIds'))
            ->setParameter('id', $report->getId())
            ->setParameter('eventIds', $eventIds);

        $queries[] = $qb->getQuery();
        $qb = $this->_em->createQueryBuilder();
        $qb->select("s.id AS event_id, t.id, v.title AS source, t.title AS name")
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->join('s.terms', 't')
            ->join('t.vocabulary', 'v')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->andWhere($qb->expr()->in('s.id', ':eventIds'))
            ->setParameter('id', $report->getId())
            ->setParameter('eventIds', $eventIds);

        $queries[] = $qb->getQuery();
        foreach ($queries as $query) {
            /* @var Query $query */
            $rhett = array_merge($rhett, $query->getResult(AbstractQuery::HYDRATE_ARRAY));
        }
        return $rhett;
    }

    /**
     * Retrieves a lookup map of given events ('sessions') in a given curriculum inventory report,
     * grouped and keyed off by sequence block id.
     * @param CurriculumInventoryReportInterface $report
     * @param array|int[] $eventIds
     * @return array
     */
    public function getEventReferencesForSequenceBlocks(
        CurriculumInventoryReportInterface $report,
        array $eventIds = []
    ) {
        if (empty($eventIds)) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('sb.id, s.id AS event_id, s.supplemental AS optional')
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->andWhere($qb->expr()->in('s.id', ':eventIds'))
            ->setParameter('id', $report->getId())
            ->setParameter('eventIds', $eventIds);

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];

        foreach ($rows as $row) {
            if (! array_key_exists($row['id'], $rhett)) {
                $rhett[$row['id']] = [];
            }
            $rhett[$row['id']][] = $row;
        }

        return $rhett;
    }

    /**
     * Retrieves all program objectives in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @return array An associative array of arrays, keyed off by objective id.
     *   Each item is an associative array, containing
     *   the objective's id, title and its ancestor's id.
     *  (keys: "objective_id", "title" and "ancestor_id").
     * @throws \Exception
     */
    public function getProgramObjectives(CurriculumInventoryReportInterface $report)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('o.id, o.title, a.id AS ancestor_id')
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('p.school', 's')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->join('c.cohorts', 'co')
            ->join('co.programYear', 'py')
            ->join('py.program', 'p2')
            ->join('p2.school', 's2')
            ->join('py.programYearObjectives', 'o')
            ->leftJoin('o.ancestor', 'a')
            ->where($qb->expr()->eq('s.id', 's2.id'))
            ->andWhere($qb->expr()->eq('r.id', ':id'))
            ->setParameter('id', $report->getId());

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];
        foreach ($rows as $row) {
            $rhett[$row['id']] = $row;
        }
        return $rhett;
    }

    /**
     * Retrieves all course objectives in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @return array an associative array of arrays, keyed off by objective id.
     *   Each item is an associative array, containing
     *   the objective's id and title (keys: "objective_id" and "title").
     * @throws \Exception
     */
    public function getCourseObjectives(CurriculumInventoryReportInterface $report)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('o.id, o.title')
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->join('c.courseObjectives', 'o')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->setParameter('id', $report->getId());

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];
        foreach ($rows as $row) {
            $rhett[$row['id']] = $row;
        }
        return $rhett;
    }

    /**
     * Retrieves all session objectives for given sessions in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array|int[] $sessionIds
     * @return array An associative array of arrays, keyed off by objective id.
     *   Each item is an associative array, containing
     *   the objective's id and title (keys: "objective_id" and "title").
     * @throws \Exception
     */
    public function getSessionObjectives(CurriculumInventoryReportInterface $report, array $sessionIds = [])
    {
        $rhett = [];

        if (empty($sessionIds)) {
            return $rhett;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('o.id, o.title')
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->join('c.sessions', 's')
            ->join('s.sessionObjectives', 'o')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->andWhere($qb->expr()->in('s.id', ':sessionIds'))
            ->setParameter('id', $report->getId())
            ->setParameter('sessionIds', $sessionIds);

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        foreach ($rows as $row) {
            $rhett[$row['id']] = $row;
        }
        return $rhett;
    }

    /**
     * Retrieves all the competency object references per given event (session) in a given report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array|int[] $consolidatedProgramObjectivesMap
     * @param array|int[] $eventIds
     * @return array An associative array of arrays, keyed off by event id.
     *     Each sub-array is in turn a two item map, containing a list of course objectives ids
     *     under 'course_objectives', a list of program objective ids under 'program_objective'
     *     and a list of session objective ids under under 'session_objective_ids'.
     *
     *   <pre>
     *   [ <sequence block id> => [
     *       "course_objectives" => [ <list of course objectives ids> ]
     *       "program_objectives" => [ <list of program objective ids> ]
     *       "session_objectives" => [ <list of session objective ids> ]
     *     ],
     *     ...
     *   ],
     *   </pre>
     * @throws \Exception
     */
    public function getCompetencyObjectReferencesForEvents(
        CurriculumInventoryReportInterface $report,
        array $consolidatedProgramObjectivesMap,
        array $eventIds = []
    ) {
        if (empty($eventIds)) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select(
            's.id AS event_id, so.id AS session_objective_id, co.id AS course_objective_id,'
            . 'po.id AS program_objective_id'
        )
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->join('c.sessions', 's')
            ->leftJoin('s.sessionObjectives', 'so')
            ->leftJoin('so.courseObjectives', 'co')
            ->leftJoin('co.programYearObjectives', 'po')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->andWhere($qb->expr()->in('s.id', ':eventIds'))
            ->setParameter('id', $report->getId())
            ->setParameter('eventIds', $eventIds);

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];
        foreach ($rows as $row) {
            $eventId = $row['event_id'];
            $sessionObjectiveId = $row['session_objective_id'];
            $courseObjectiveId = $row['course_objective_id'];
            $programObjectiveId = $row['program_objective_id'];
            if (array_key_exists($programObjectiveId, $consolidatedProgramObjectivesMap)) {
                $programObjectiveId = $consolidatedProgramObjectivesMap[$programObjectiveId];
            }
            if (! array_key_exists($eventId, $rhett)) {
                $rhett[$eventId] = [
                    'session_objectives' => [],
                    'course_objectives' => [],
                    'program_objectives' => [],
                ];
            }
            if (
                isset($sessionObjectiveId)
                && ! in_array($sessionObjectiveId, $rhett[$eventId]['session_objectives'])
            ) {
                $rhett[$eventId]['session_objectives'][] = $sessionObjectiveId;
            }
            if (
                isset($courseObjectiveId)
                && ! in_array($courseObjectiveId, $rhett[$eventId]['course_objectives'])
            ) {
                $rhett[$eventId]['course_objectives'][] = $courseObjectiveId;
            }
            if (
                isset($programObjectiveId)
                && ! in_array($programObjectiveId, $rhett[$eventId]['program_objectives'])
            ) {
                $rhett[$eventId]['program_objectives'][] = $programObjectiveId;
            }
        }

        return $rhett;
    }

    /**
     * Retrieves all the competency object references per sequence block in a given report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array|int[] $consolidatedProgramObjectivesMap
     * @return array An associative array of arrays, keyed off by sequence block id.
     *     Each sub-array is in turn a two item map, containing a list of course objectives ids
     *     under 'course_objectives' and a list of program objective ids under 'program_objective'.
     *
     *   <pre>
     *   [ <sequence block id> => [
     *       "course_objectives" => [ <list of course objectives ids> ]
     *       "program_objectives" => [ <list of program objective ids> ]
     *     ],
     *     ...
     *   ],
     *   </pre>
     * @throws \Exception
     */
    public function getCompetencyObjectReferencesForSequenceBlocks(
        CurriculumInventoryReportInterface $report,
        array $consolidatedProgramObjectivesMap
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('sb.id, co.id AS course_objective_id, po.id AS program_objective_id')
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('p.school', 's')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->leftJoin('c.courseObjectives', 'co')
            ->leftJoin('co.programYearObjectives', 'po')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->setParameter('id', $report->getId());

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];

        foreach ($rows as $row) {
            $sequenceBlockId = $row['id'];
            $courseObjectiveId = $row['course_objective_id'];
            $programObjectiveId = $row['program_objective_id'];
            if (array_key_exists($programObjectiveId, $consolidatedProgramObjectivesMap)) {
                $programObjectiveId = $consolidatedProgramObjectivesMap[$programObjectiveId];
            }
            if (! array_key_exists($sequenceBlockId, $rhett)) {
                $rhett[$sequenceBlockId] = [
                    'course_objectives' => [],
                    'program_objectives' => [],
                ];
            }
            if (
                isset($courseObjectiveId)
                && ! in_array($courseObjectiveId, $rhett[$sequenceBlockId]['course_objectives'])
            ) {
                $rhett[$sequenceBlockId]['course_objectives'][] = $courseObjectiveId;
            }
            if (
                isset($programObjectiveId)
                && ! in_array($programObjectiveId, $rhett[$sequenceBlockId]['program_objectives'])
            ) {
                $rhett[$sequenceBlockId]['program_objectives'][] = $programObjectiveId;
            }
        }

        return $rhett;
    }

    /**
     * Retrieves the relations between given program-objectives and PCRS (via competencies).
     * @param array|int[] $programObjectiveIds
     * @param array|int[] $pcrsIds
     * @param array|int[] $consolidatedProgramObjectivesMap
     * @return array
     * @throws \Exception
     */
    public function getProgramObjectivesToPcrsRelations(
        array $programObjectiveIds,
        array $pcrsIds,
        array $consolidatedProgramObjectivesMap
    ) {
        $rhett = [
            'relations' => [],
            'program_objective_ids' => [],
            'pcrs_ids' => [],
        ];

        if (! count($programObjectiveIds) || ! count($pcrsIds)) {
            return $rhett;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('o.id as objective_id, am.id AS pcrs_id')
            ->distinct()
            ->from(ProgramYearObjective::class, 'o')
            ->join('o.competency', 'c')
            ->join('c.aamcPcrses', 'am')
            ->where($qb->expr()->in('am.id', ':pcrs'))
            ->andWhere($qb->expr()->in('o.id', ':objectives'))
            ->setParameter(':pcrs', $pcrsIds)
            ->setParameter(':objectives', $programObjectiveIds);

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        foreach ($rows as $row) {
            $pcrsId = $row['pcrs_id'];
            $objectiveId = $row['objective_id'];
            // ignore substituted objectives here, in order to prevent
            // false objective-to-PCRS relationships from being reported out.
            if (array_key_exists($objectiveId, $consolidatedProgramObjectivesMap)) {
                continue;
            }
            $rhett['relations'][] = [
                'rel1' => $objectiveId,
                'rel2' => $pcrsId,
            ];
            $rhett['program_objective_ids'][] = $objectiveId;
            $rhett['pcrs_ids'][] = $pcrsId;
        }

        // dedupe
        $rhett['program_objective_ids'] = array_values(array_unique($rhett['program_objective_ids']));
        $rhett['pcrs_ids'] = array_values(array_unique($rhett['pcrs_ids']));

        return $rhett;
    }



    /**
     * Retrieves the relations between given course- and program-objectives.
     * @param array|int[] $courseObjectiveIds
     * @param array|int[] $programObjectiveIds
     * @param array|int[] $consolidatedProgramObjectivesMap
     * @return array
     * @throws \Exception
     */
    public function getCourseObjectivesToProgramObjectivesRelations(
        array $courseObjectiveIds,
        array $programObjectiveIds,
        array $consolidatedProgramObjectivesMap
    ) {
        $rhett = [
            'relations' => [],
            'course_objective_ids' => [],
            'program_objective_ids' => [],
        ];

        if (! count($courseObjectiveIds) || ! count($programObjectiveIds)) {
            return $rhett;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('o.id AS objective_id, p.id AS program_objective_id')
            ->distinct()
            ->from(CourseObjective::class, 'o')
            ->join('o.programYearObjectives', 'p')
            ->where($qb->expr()->in('p.id', ':programObjectiveIds'))
            ->andWhere($qb->expr()->in('o.id', ':courseObjectiveIds'))
            ->setParameter(':courseObjectiveIds', $courseObjectiveIds)
            ->setParameter(':programObjectiveIds', $programObjectiveIds);

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        foreach ($rows as $row) {
            $programObjectiveId = $row['program_objective_id'];
            $courseObjectiveId = $row['objective_id'];
            if (array_key_exists($programObjectiveId, $consolidatedProgramObjectivesMap)) {
                $programObjectiveId = $consolidatedProgramObjectivesMap[$programObjectiveId];
            }
            $relKey = $programObjectiveId . ':' . $courseObjectiveId; // poor man's way to avoid duplication
            $rhett['relations'][$relKey] = [
                'rel1' => $programObjectiveId,
                'rel2' => $courseObjectiveId,
            ];

            $rhett['course_objective_ids'][] = $courseObjectiveId;
            $rhett['program_objective_ids'][] = $programObjectiveId;
        }

        // dedupe
        $rhett['course_objective_ids'] = array_values(array_unique($rhett['course_objective_ids']));
        $rhett['program_objective_ids'] = array_values(array_unique($rhett['program_objective_ids']));

        // lose the temp key
        $rhett['relations'] = array_values($rhett['relations']);

        return $rhett;
    }

    /**
     * Retrieves the relations between given session- and course-objectives.
     *
     * @param array|int[] $sessionObjectiveIds
     * @param array|int[] $courseObjectiveIds
     * @return array
     * @throws \Exception
     */
    public function getSessionObjectivesToCourseObjectivesRelations(
        array $sessionObjectiveIds,
        array $courseObjectiveIds
    ) {
        $rhett = [
            'relations' => [],
            'session_objective_ids' => [],
            'course_objective_ids' => [],
        ];

        if (! count($sessionObjectiveIds) || ! count($courseObjectiveIds)) {
            return $rhett;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('o.id AS objective_id, c.id AS course_objective_id')
            ->distinct()
            ->from(SessionObjective::class, 'o')
            ->join('o.courseObjectives', 'c')
            ->where($qb->expr()->in('c.id', ':courseObjectiveIds'))
            ->andWhere($qb->expr()->in('o.id', ':sessionObjectiveIds'))
            ->setParameter(':sessionObjectiveIds', $sessionObjectiveIds)
            ->setParameter(':courseObjectiveIds', $courseObjectiveIds);

        $rows =  $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        foreach ($rows as $row) {
            $rhett['relations'][] = [
                'rel1' => $row['course_objective_id'],
                'rel2' => $row['objective_id'],
            ];
            $rhett['session_objective_ids'][] = $row['objective_id'];
            $rhett['course_objective_ids'][] = $row['course_objective_id'];
        }

        // dedupe
        $rhett['session_objective_ids'] = array_values(array_unique($rhett['session_objective_ids']));
        $rhett['course_objective_ids'] = array_values(array_unique($rhett['course_objective_ids']));

        return $rhett;
    }

    /**
     * Retrieves all PCRS linked to sequence blocks (via objectives and competencies) in a given inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @return array A nested array of associative arrays, keyed off by 'pcrs_id'. Each sub-array represents a PCRS
     *    and is itself an associative array with values being keyed off by 'pcrs_id' and 'description'.
     * @throws \Exception
     */
    public function getPcrs(CurriculumInventoryReportInterface $report)
    {
        $rhett = [];
        $qb = $this->_em->createQueryBuilder();
        $qb->select('am.id AS pcrs_id, am.description')
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('p.school', 's')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->join('c.cohorts', 'co')
            ->join('co.programYear', 'py')
            ->join('py.programYearObjectives', 'pyxo')
            ->join('pyxo.competency', 'cm')
            ->join('cm.school', 's2')
            ->join('cm.parent', 'cm2')
            ->join('cm2.aamcPcrses', 'am')
            ->where($qb->expr()->eq('s.id', 's2.id'))
            ->andWhere($qb->expr()->eq('r.id', ':id'))
            ->setParameter(':id', $report->getId());
        $queries[] = $qb->getQuery();

        $qb = $this->_em->createQueryBuilder();
        $qb->select('am.id AS pcrs_id, am.description')
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.program', 'p')
            ->join('p.school', 's')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.course', 'c')
            ->join('c.cohorts', 'co')
            ->join('co.programYear', 'py')
            ->join('py.programYearObjectives', 'pyxo')
            ->join('pyxo.competency', 'cm')
            ->join('cm.school', 's2')
            ->join('cm.aamcPcrses', 'am')
            ->where($qb->expr()->eq('s.id', 's2.id'))
            ->andWhere($qb->expr()->eq('r.id', ':id'))
            ->setParameter(':id', $report->getId());
        $queries[] = $qb->getQuery();

        foreach ($queries as $query) {
            /* @var Query $query */
            $rows = $query->getResult(AbstractQuery::HYDRATE_ARRAY);
            foreach ($rows as $row) {
                $rhett[$row['pcrs_id']] = $row;
            }
        }
        return $rhett;
    }

    /**
     * Retrieves a list of events derived from independent learning sessions in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array $excludedSessionIds The ids of sessions that are flagged to be excluded from this report.
     * @return array An assoc. array of assoc. arrays, each item representing an event, keyed off by event id.
     * @throws \Exception
     */
    public function getEventsFromIlmOnlySessions(
        CurriculumInventoryReportInterface $report,
        array $excludedSessionIds = []
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(
            's.id AS event_id, s.title, s.description, am.id AS method_id,'
            . 'st.assessment AS is_assessment_method, ao.name AS assessment_option_name, sf.hours'
        )
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('s.ilmSession', 'sf')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->leftJoin('s.offerings', 'o')
            ->leftJoin('s.sessionType', 'st')
            ->leftJoin('st.aamcMethods', 'am')
            ->leftJoin('st.assessmentOption', 'ao')
            ->where($qb->expr()->eq('s.published', 1))
            ->andWhere($qb->expr()->isNull('o.id'))
            ->andWhere($qb->expr()->eq('r.id', ':id'))
            ->groupBy('s.id')
            ->addGroupBy('s.title')
            ->addGroupBy('s.description')
            ->addGroupBy('am.id')
            ->addGroupBy('st.assessment')
            ->setParameter(':id', $report->getId());

        if (! empty($excludedSessionIds)) {
            $qb->andWhere($qb->expr()->notIn('s.id', ':excludedSessions'))
                ->setParameter(':excludedSessions', $excludedSessionIds);
        }

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];
        foreach ($rows as $row) {
            $row['duration'] = floor($row['hours'] * 60); // convert from hours to minutes
            unset($row['hours']);
            $rhett[$row['event_id']] = $row;
        }
        return $rhett;
    }

    /**
     * Retrieves a list of events (derived from published sessions/offerings)
     * in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array $sessionIds The ids of sessions that are flagged to have their offerings counted as one.
     * @param array $excludedSessionIds The ids of sessions that are flagged to be excluded from this report.
     * @return array An assoc. array of assoc. arrays, each item representing an event, keyed off by event id.
     * @throws \Exception
     */
    public function getEventsFromOfferingsOnlySessions(
        CurriculumInventoryReportInterface $report,
        array $sessionIds = [],
        array $excludedSessionIds = []
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(
            's.id AS event_id, s.title, s.description, am.id AS method_id,'
            . 'st.assessment AS is_assessment_method, ao.name AS assessment_option_name, o.startDate, o.endDate'
        )
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->join('s.offerings', 'o')
            ->leftJoin('s.ilmSession', 'sf')
            ->leftJoin('s.sessionType', 'st')
            ->leftJoin('st.aamcMethods', 'am')
            ->leftJoin('st.assessmentOption', 'ao')
            ->where($qb->expr()->eq('s.published', 1))
            ->andWhere($qb->expr()->isNull('sf.id'))
            ->andWhere($qb->expr()->eq('r.id', ':id'))
            ->setParameter(':id', $report->getId());

        if (! empty($excludedSessionIds)) {
            $qb->andWhere($qb->expr()->notIn('s.id', ':excludedSessions'))
                ->setParameter(':excludedSessions', $excludedSessionIds);
        }

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        $rhett = [];
        foreach ($rows as $row) {
            $row['duration'] = 0;
            if ($row['startDate']) {
                /* @var \DateTime $startDate */
                $startDate = $row['startDate'];
                /* @var \DateTime $endDate */
                $endDate = $row['endDate'];
                $duration = floor(($endDate->getTimestamp() - $startDate->getTimestamp()) / 60);
                $row['duration'] = $duration;
            }

            if (!array_key_exists($row['event_id'], $rhett)) {
                $rhett[$row['event_id']] = $row;
            } elseif (in_array($row['event_id'], $sessionIds)) {
                if ($rhett[$row['event_id']]['duration'] < $row['duration']) {
                    $rhett[$row['event_id']]['duration'] = $row['duration'];
                }
            } else {
                $rhett[$row['event_id']]['duration'] += $row['duration'];
            }
        }

        array_walk($rhett, function (&$row) {
            unset($row['startDate']);
            unset($row['endDate']);
        });
        return $rhett;
    }

    /**
     * Retrieves a list of events (derived from published ILM sessions with offerings)
     * in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @param array $sessionIds The ids of sessions that are flagged to have their offerings counted as one.
     * @param array $excludedSessionIds The ids of sessions that are flagged to be excluded from this report.
     * @return array An assoc. array of assoc. arrays, each item representing an event, keyed off by event id.
     * @throws \Exception
     */
    public function getEventsFromIlmSessionsWithOfferings(
        CurriculumInventoryReportInterface $report,
        array $sessionIds = [],
        array $excludedSessionIds = []
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(
            's.id AS event_id, s.title, s.description, am.id AS method_id, sf.hours as ilm_hours,'
            . 'st.assessment AS is_assessment_method, ao.name AS assessment_option_name, o.startDate, o.endDate'
        )
            ->from(Session::class, 's')
            ->join('s.course', 'c')
            ->join('c.sequenceBlocks', 'sb')
            ->join('sb.report', 'r')
            ->join('s.offerings', 'o')
            ->join('s.ilmSession', 'sf')
            ->leftJoin('s.sessionType', 'st')
            ->leftJoin('st.aamcMethods', 'am')
            ->leftJoin('st.assessmentOption', 'ao')
            ->where($qb->expr()->eq('s.published', 1))
            ->andWhere($qb->expr()->eq('r.id', ':id'))
            ->setParameter(':id', $report->getId());

        if (! empty($excludedSessionIds)) {
            $qb->andWhere($qb->expr()->notIn('s.id', ':excludedSessions'))
                ->setParameter(':excludedSessions', $excludedSessionIds);
        }

        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $rhett = [];

        $ilmHours = [];
        foreach ($rows as $row) {
            $ilmHours[$row['event_id']] =  floor($row['ilm_hours'] * 60);
            $row['duration'] = 0;
            if ($row['startDate']) {
                /* @var \DateTime $startDate */
                $startDate = $row['startDate'];
                /* @var \DateTime $endDate */
                $endDate = $row['endDate'];
                $duration = floor(($endDate->getTimestamp() - $startDate->getTimestamp()) / 60);
                $row['duration'] = $duration;
            }

            if (!array_key_exists($row['event_id'], $rhett)) {
                $rhett[$row['event_id']] = $row;
            } elseif (in_array($row['event_id'], $sessionIds)) {
                if ($rhett[$row['event_id']]['duration'] < $row['duration']) {
                    $rhett[$row['event_id']]['duration'] = $row['duration'];
                }
            } else {
                $rhett[$row['event_id']]['duration'] += $row['duration'];
            }
        }

        array_walk($rhett, function (&$row) use ($ilmHours) {
            $row['duration'] = $row['duration'] + $ilmHours[$row['event_id']];
            unset($row['startDate']);
            unset($row['endDate']);
            unset($row['ilm_hours']);
        });

        return $rhett;
    }

    /**
     * Get all ids of sessions that are flagged to have their offerings counted as one in the given report.
     * @param CurriculumInventoryReportInterface $report
     * @return array|int[]
     */
    public function getCountForOneOfferingSessionIds(CurriculumInventoryReportInterface $report)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('s.id')
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.sessions', 's')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->setParameter(':id', $report->getId());
        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_column($rows, 'id');
    }

    /**
     * Get all ids of sessions that are flagged to be excluded from the given report.
     * @param CurriculumInventoryReportInterface $report
     * @return array|int[]
     */
    public function getExcludedSessionIds(CurriculumInventoryReportInterface $report)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('s.id')
            ->distinct()
            ->from(CurriculumInventoryReport::class, 'r')
            ->join('r.sequenceBlocks', 'sb')
            ->join('sb.excludedSessions', 's')
            ->where($qb->expr()->eq('r.id', ':id'))
            ->setParameter(':id', $report->getId());
        $rows = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_column($rows, 'id');
    }

    /**
     * Retrieves a list of events (derived from published sessions/offerings and independent learning sessions)
     * in a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @return array An assoc. array of assoc. arrays, each item representing an event, keyed off by event id.
     * @throws \Exception
     */
    public function getEvents(CurriculumInventoryReportInterface $report)
    {
        // WHAT'S GOING ON HERE?!
        // Aggregate the CI events retrieved from session-offerings with the events retrieved from ILM sessions,
        // and sessions that are ILMs with offerings.
        // We can't do this by ways of <code>array_merge()</code>, since this would clobber the keys on the joined array
        // (we're dealing with associative arrays using numeric keys here).
        // Hence the use of the '+' array-operator.
        // This should be OK since there is no overlap of elements between the various source arrays.
        // [ST 2015/09/18]
        // @link http://php.net/manual/en/language.operators.array.php
        // @link http://php.net/manual/en/function.array-merge.php
        $sessionIds = $this->getCountForOneOfferingSessionIds($report);
        $excludedSessionids = $this->getExcludedSessionIds($report);
        $rhett = $this->getEventsFromOfferingsOnlySessions($report, $sessionIds, $excludedSessionids)
            + $this->getEventsFromIlmOnlySessions($report, $excludedSessionids)
            + $this->getEventsFromIlmSessionsWithOfferings($report, $sessionIds, $excludedSessionids);
        ksort($rhett);
        return $rhett;
    }
}
