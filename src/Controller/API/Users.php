<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Classes\SessionUserInterface;
use App\RelationshipVoter\AbstractVoter;
use App\Repository\UserRepository;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UsersController
 * We have to handle a special 'q' parameter
 * as well as special handling for ICS feed keys
 * so users needs its own controller
 *
 * @Route("/api/{version<v1|v3>}/users")
 */

class Users extends ReadWriteController
{
    /**
     * @var UserRepository
     */
    protected $manager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        UserRepository $repository,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    ) {
        parent::__construct($repository, 'users');
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * Handle the special 'q' parameter for courses
     * @Route("", methods={"GET"})
     */
    public function getAll(
        string $version,
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        $q = $request->get('q');
        $parameters = ApiRequestParser::extractParameters($request);

        if (null !== $q && '' !== $q) {
            $dtos = $this->manager->findUserDTOsByQ(
                $q,
                $parameters['orderBy'],
                $parameters['limit'],
                $parameters['offset'],
                $parameters['criteria'],
            );

            $filteredResults = array_filter($dtos, function ($object) use ($authorizationChecker) {
                return $authorizationChecker->isGranted(AbstractVoter::VIEW, $object);
            });

            //Re-index numerically index the array
            $values = array_values($filteredResults);

            return $builder->buildResponseForGetAllRequest($this->endpoint, $values, Response::HTTP_OK, $request);
        }

        return parent::getAll($version, $request, $authorizationChecker, $builder);
    }

    /**
     * When Users are submitted with an empty icsFeedKey value that overrides
     * the created key.  This happens when new users are created and they don't have a
     * key yet.  Instead of using the blank key we need to keep the one that is generated
     * in the User entity constructor.
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
        $data = $requestParser->extractPostDataFromRequest($request, $this->endpoint);
        $dataWithoutEmptyIcsFeed = array_map(function ($obj) {
            if (is_object($obj) && property_exists($obj, 'icsFeedKey')) {
                if (empty($obj->icsFeedKey)) {
                    unset($obj->icsFeedKey);
                }
            }

            return $obj;
        }, $data);

        $class = $this->manager->getClass() . '[]';
        $json = json_encode($dataWithoutEmptyIcsFeed);
        $entities = $this->serializer->deserialize($json, $class, 'json');

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
            $this->manager->update($entity, false);
        }
        $this->manager->flush();

        $dtos = $this->fetchDtosForEntities($entities);

        return $builder->buildResponseForPostRequest($this->endpoint, $dtos, Response::HTTP_CREATED, $request);
    }

    /**
     * Only a root user can make other users root.
     * This has to be done here because by the time it reaches the voter the
     * current user object in the session has been modified
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
        $entity = $this->manager->findOneBy(['id' => $id]);
        if ($entity) {
            $obj = $requestParser->extractPutDataFromRequest($request, $this->endpoint);
            /** @var SessionUserInterface $sessionUser */
            $sessionUser = $this->tokenStorage->getToken()->getUser();
            if (
                $obj->root &&
                (!$sessionUser->isRoot() && !$entity->isRoot())
            ) {
                throw new AccessDeniedException('Unauthorized access!');
            }
        }

        return parent::put($version, $id, $request, $requestParser, $validator, $authorizationChecker, $builder);
    }
}
