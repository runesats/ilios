<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\CohortInterface;
use App\Entity\DTO\ProgramYearDTO;
use App\Entity\Manager\CohortManager;
use App\Entity\Manager\ProgramYearManager;
use App\Entity\ProgramYearInterface;
use App\RelationshipVoter\AbstractVoter;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/{version<v1|v2>}/programyears")
 */
class ProgramYears extends ReadWriteController
{
    /**
     * @var ProgramYearManager
     */
    protected $manager;

    /**
     * @var CohortManager
     */
    protected $cohortManager;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        ProgramYearManager $manager,
        CohortManager $cohortManager,
        SerializerInterface $serializer
    ) {
        parent::__construct($manager, 'programyears');
        $this->cohortManager = $cohortManager;
        $this->serializer = $serializer;
    }

    /**
     * Create cohort to match the new program year
     * @Route("/", methods={"POST"})
     */
    public function post(
        string $version,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ) {
        $data = $requestParser->extractPostDataFromRequest($request, $this->endpoint);
        // remove empty cohorts since we will be creating them later
        $cleanData = array_map(function ($obj) {
            if (empty($obj->cohort)) {
                unset($obj->cohort);
            }

            return $obj;
        }, $data);

        $class = $this->manager->getClass() . '[]';
        $entities = $this->serializer->deserialize(json_encode($cleanData), $class, 'json');

        foreach ($entities as $entity) {
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
            }
            if (! $authorizationChecker->isGranted(AbstractVoter::CREATE, $entity)) {
                throw new AccessDeniedException('Unauthorized access!');
            }

            $this->manager->update($entity, false);
            $this->createCohort($entity);
        }
        $this->manager->flush();

        return $builder->buildPluralResponse($this->endpoint, $entities, Response::HTTP_CREATED);
    }

    /**
     * Modifies a single object in the API.  Can also create and
     * object if it does not yet exist.
     * @Route("/{id}", methods={"PUT"})
     */
    public function put(
        string $version,
        string $id,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ) {
        /** @var ProgramYearInterface $entity */
        $entity = $this->manager->findOneBy(['id' => $id]);

        if ($entity) {
            $code = Response::HTTP_OK;
            $permission = AbstractVoter::EDIT;
            $data = $requestParser->extractPutDataFromRequest($request, $this->endpoint);
            if (!$entity->isArchived() && $data->archived) {
                return $this->archiveProgramYear($entity, $builder, $authorizationChecker);
            }
            if ($entity->isLocked() && !$data->locked) {
                return $this->unlockProgramYear($entity, $builder, $authorizationChecker);
            }
            if (!$entity->isLocked() && $data->locked) {
                return $this->lockProgramYear($entity, $builder, $authorizationChecker);
            }
        } else {
            $entity = $this->manager->create();
            $code = Response::HTTP_CREATED;
            $permission = AbstractVoter::CREATE;
        }

        $entity = $requestParser->extractEntityFromPutRequest($request, $entity, $this->endpoint);

        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
        }
        if (! $authorizationChecker->isGranted($permission, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        $this->manager->update($entity, false, false);
        if (empty($entity->getCohort())) {
            $this->createCohort($entity);
        }

        $this->manager->flush();

        return $builder->buildSingularResponse($this->endpoint, $entity, $code);
    }

    /**
     * @Route("/{id}/downloadobjectivesmapping", methods={"GET"})
     */
    public function downloadCourseObjectivesReport(
        string $version,
        int $id
    ): Response {
        /** @var ProgramYearDTO $dto */
        $dto = $this->manager->findDTOBy(['id' => $id]);

        if (! $dto) {
            throw new NotFoundHttpException(sprintf("%s/%s was not found.", $this->endpoint, $id));
        }

        $data = $this->manager->getProgramYearObjectiveToCourseObjectivesMapping($dto->id);

        array_walk($data, function (&$row) {
            foreach (['program_year_objective', 'mapped_course_objective'] as $key) {
                if ($row[$key]) {
                    $row[$key] = strip_tags($row[$key]);
                }
            }
            $row['matriculation_year'] = $row['matriculation_year'] . ' - ' . ($row['matriculation_year'] + 1);
        });

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        return new Response(
            $serializer->serialize($data, 'csv'),
            Response::HTTP_OK,
            [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'inline',
            ]
        );
    }

    /**
     * Creates a new cohort for a new program year.
     * @param ProgramYearInterface $programYear
     */
    protected function createCohort(ProgramYearInterface $programYear)
    {
        $program = $programYear->getProgram();
        $graduationYear = $programYear->getStartYear() + $program->getDuration();

        /* @var CohortInterface $cohort */
        $cohort = $this->cohortManager->create();
        $cohort->setTitle("Class of ${graduationYear}");
        $cohort->setProgramYear($programYear);
        $programYear->setCohort($cohort);

        $this->cohortManager->update($cohort, false, false);
    }

    protected function archiveProgramYear(
        ProgramYearInterface $entity,
        ApiResponseBuilder $builder,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        if (!$authorizationChecker->isGranted(AbstractVoter::ARCHIVE, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }
        $entity->setArchived(true);
        $this->manager->update($entity, true, false);

        return $builder->buildSingularResponse($this->endpoint, $entity, Response::HTTP_OK);
    }

    protected function lockProgramYear(
        ProgramYearInterface $entity,
        ApiResponseBuilder $builder,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        if (!$authorizationChecker->isGranted(AbstractVoter::LOCK, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }
        $entity->setLocked(true);
        $this->manager->update($entity, true, false);

        return $builder->buildSingularResponse($this->endpoint, $entity, Response::HTTP_OK);
    }

    protected function unlockProgramYear(
        ProgramYearInterface $entity,
        ApiResponseBuilder $builder,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        if (!$authorizationChecker->isGranted(AbstractVoter::UNLOCK, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }
        $entity->setLocked(false);
        $this->manager->update($entity, true, false);

        return $builder->buildSingularResponse($this->endpoint, $entity, Response::HTTP_OK);
    }
}
