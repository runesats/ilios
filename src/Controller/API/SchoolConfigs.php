<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\DTO\SchoolConfigDTO;
use App\Repository\SchoolConfigRepository;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name:'School configuration')]
#[Route('/api/{version<v3>}/schoolconfigs')]
class SchoolConfigs extends AbstractApiController
{
    public function __construct(SchoolConfigRepository $repository)
    {
        parent::__construct($repository, 'schoolconfigs');
    }

    #[Route(
        '/{id}',
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/api/{version}/schoolconfigs/{id}',
        summary: 'Fetch a single school configuration item.',
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path')
        ]
    )]
    #[OA\Response(
        response: '200',
        description: 'A single school configuration item.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'schoolConfigs',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: SchoolConfigDTO::class)
                    )
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(response: '404', description: 'Not found.')]
    public function getOne(
        string $version,
        string $id,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder,
        Request $request
    ): Response {
        return $this->handleGetOne($version, $id, $authorizationChecker, $builder, $request);
    }

    #[Route(
        methods: ['GET']
    )]
    #[OA\Get(
        path: "/api/{version}/schoolconfigs",
        summary: "Fetch all school configuration items.",
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(
                name: 'offset',
                description: 'Offset',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Limit results',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'order_by',
                description: 'Order by fields. Must be an array, i.e. <code>&order_by[id]=ASC&order_by[x]=DESC</code>',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                ),
                style: "deepObject"
            ),
            new OA\Parameter(
                name: 'filters',
                description: 'Filter by fields. Must be an array, i.e. <code>&filters[id]=3</code>',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                ),
                style: "deepObject"
            )
        ]
    )]
    #[OA\Response(
        response: '200',
        description: 'An array of school configuration items.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'schoolConfigs',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: SchoolConfigDTO::class)
                    )
                )
            ],
            type: 'object'
        )
    )]
    public function getAll(
        string $version,
        Request $request,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        return $this->handleGetAll($version, $request, $authorizationChecker, $builder);
    }

    #[Route(methods: ['POST'])]
    #[OA\Post(
        path: '/api/{version}/schoolconfigs',
        summary: "Create school configuration items.",
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(
                name: 'body',
                in: 'body',
                required: true,
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            'schoolConfigs',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: SchoolConfigDTO::class)
                            )
                        )
                    ],
                    type: 'object',
                )
            )
        ]
    )]
    #[OA\Response(
        response: '201',
        description: 'An array of newly created school configurations.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'schoolConfigs',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: SchoolConfigDTO::class)
                    )
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(response: '400', description: 'Bad Request Data.')]
    #[OA\Response(response: '403', description: 'Access Denied.')]
    public function post(
        string $version,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        return $this->handlePost($version, $request, $requestParser, $validator, $authorizationChecker, $builder);
    }

    #[Route(
        '/{id}',
        methods: ['PUT']
    )]
    #[OA\Put(
        path: '/api/{version}/schoolconfigs/{id}',
        summary: 'Update a school configuration item.',
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path'),
            new OA\Parameter(
                name: 'body',
                in: 'body',
                required: true,
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            'schoolConfig',
                            ref: new Model(type: SchoolConfigDTO::class),
                            type: 'object'
                        )
                    ],
                    type: 'object',
                )
            )
        ]
    )]
    #[OA\Response(
        response: '200',
        description: 'The updated school configuration item.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'schoolConfig',
                    ref: new Model(type: SchoolConfigDTO::class)
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(response: '400', description: 'Bad Request Data.')]
    #[OA\Response(response: '403', description: 'Access Denied.')]
    #[OA\Response(response: '404', description: 'Not Found.')]
    public function put(
        string $version,
        string $id,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        return $this->handlePut($version, $id, $request, $requestParser, $validator, $authorizationChecker, $builder);
    }

    #[Route(
        '/{id}',
        methods: ['PATCH']
    )]
    public function patch(
        string $version,
        string $id,
        Request $request,
        ApiRequestParser $requestParser,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker,
        ApiResponseBuilder $builder
    ): Response {
        return $this->handlePatch($version, $id, $request, $requestParser, $validator, $authorizationChecker, $builder);
    }

    #[Route(
        '/{id}',
        methods: ['DELETE']
    )]
    #[OA\Delete(
        path: '/api/{version}/schoolconfigs/{id}',
        summary: 'Delete a session configuration item.',
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path')
        ]
    )]
    #[OA\Response(response: '204', description: 'Deleted.')]
    #[OA\Response(response: '403', description: 'Access Denied.')]
    #[OA\Response(response: '404', description: 'Not Found.')]
    #[OA\Response(
        response: '500',
        description: 'Deletion failed (usually caused by non-cascading relationships).'
    )]
    public function delete(
        string $version,
        string $id,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        return $this->handleDelete($version, $id, $authorizationChecker);
    }
}
