<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\RelationshipVoter\AbstractVoter;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;
use RuntimeException;

abstract class ReadWriteController extends ReadOnlyController
{
    /**
     * Handles POST which creates new data in the API
     * @Route("", methods={"POST"})
     */
    public function post(
        string $version,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        $class = $this->repository->getClass() . '[]';

        $entities = $requestParser->extractEntitiesFromPostRequest($request, $class, $this->endpoint);

        foreach ($entities as $entity) {
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;

                throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
            }
            if (! $authorizationChecker->isGranted(AbstractVoter::CREATE, $entity)) {
                throw new AccessDeniedException('Unauthorized access!');
            }
        }

        foreach ($entities as $entity) {
            $this->repository->update($entity, false);
        }
        $this->repository->flush();

        $dtos = $this->fetchDtosForEntities($entities);

        return $builder->buildResponseForPostRequest($this->endpoint, $dtos, Response::HTTP_CREATED, $request);
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
    ): Response {
        $type = $request->getAcceptableContentTypes();
        if (in_array("application/vnd.api+json", $type)) {
            throw new BadRequestHttpException("PUT is not allowed for JSON:API requests, use PATCH instead");
        }
        $entity = $this->repository->findOneBy(['id' => $id]);
        if ($entity) {
            $code = Response::HTTP_OK;
            $permission = AbstractVoter::EDIT;
        } else {
            $entity = $this->repository->create();
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

        $this->repository->update($entity, true, false);

        return $builder->buildResponseForPutRequest($this->endpoint, $entity, $code, $request);
    }

    /**
     * Modifies a single object in the API.  Can also create and
     * object if it does not yet exist.
     * @Route("/{id}", methods={"PATCH"})
     */
    public function patch(
        string $version,
        string $id,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        $type = $request->getAcceptableContentTypes();
        if (!in_array("application/vnd.api+json", $type)) {
            throw new BadRequestHttpException("PATCH is only allowed for JSON:API requests, use PUT instead");
        }

        $entity = $this->repository->findOneBy(['id' => $id]);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf("%s/%s was not found.", $this->endpoint, $id));
        }

        $requestParser->extractEntityFromPutRequest($request, $entity, $this->endpoint);

        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            throw new HttpException(Response::HTTP_BAD_REQUEST, $errorsString);
        }
        if (! $authorizationChecker->isGranted(AbstractVoter::EDIT, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        $this->repository->update($entity, true, false);

        $dtos = $this->fetchDtosForEntities([$entity]);
        return $builder->buildResponseForPatchRequest($this->endpoint, $dtos[0], Response::HTTP_OK, $request);
    }


    /**
     * Handles DELETE requests to remove an element from the API
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(
        string $version,
        string $id,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        $entity = $this->repository->findOneBy(['id' => $id]);

        if (! $entity) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        if (! $authorizationChecker->isGranted(AbstractVoter::DELETE, $entity)) {
            throw new AccessDeniedException('Unauthorized access!');
        }

        try {
            $this->repository->delete($entity);

            return new Response('', Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            throw new RuntimeException("Failed to delete entity: " . $exception->getMessage());
        }
    }

    protected function fetchDtosForEntities(array $entities): array
    {
        //read and deliver DTOs instead of Entities
        $idField = $this->repository->getIdField();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $ids = array_map(function ($entity) use ($idField, $propertyAccessor) {
            return $propertyAccessor->getValue($entity, $idField);
        }, $entities);

        return $this->repository->findDTOsBy(['id' => $ids]);
    }
}
