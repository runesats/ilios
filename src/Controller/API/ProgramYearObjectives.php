<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\DTO\ProgramYearObjectiveDTO;
use App\Repository\ProgramYearObjectiveRepository;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name:'Program year objectives')]
#[Route('/api/{version<v3>}/programyearobjectives')]
class ProgramYearObjectives extends AbstractApiController
{
    public function __construct(ProgramYearObjectiveRepository $repository)
    {
        parent::__construct($repository, 'programyearobjectives');
    }

    #[Route(
        '/{id}',
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/api/{version}/programyearobjectives/{id}',
        summary: 'Fetch a single program year objective.',
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path')
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'A single program year objective.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'programYearObjectives',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: ProgramYearObjectiveDTO::class)
                            )
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: '404', description: 'Not found.')
        ]
    )]
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
        path: "/api/{version}/programyearobjectives",
        summary: "Fetch all program year objectives.",
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
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'An array of program year objectives.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'programYearObjectives',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: ProgramYearObjectiveDTO::class)
                            )
                        )
                    ],
                    type: 'object'
                )
            )
        ]
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
        path: '/api/{version}/programyearobjectives',
        summary: "Create program year objectives.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        'programYearObjectives',
                        type: 'array',
                        items: new OA\Items(
                            ref: new Model(type: ProgramYearObjectiveDTO::class)
                        )
                    )
                ],
                type: 'object',
            )
        ),
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path')
        ],
        responses: [
            new OA\Response(
                response: '201',
                description: 'An array of newly created program year objectives.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'programYearObjectives',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: ProgramYearObjectiveDTO::class)
                            )
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: '400', description: 'Bad Request Data.'),
            new OA\Response(response: '403', description: 'Access Denied.')
        ]
    )]
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
        path: '/api/{version}/programyearobjectives/{id}',
        summary: 'Update or create a program year objective.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        'programYearObjective',
                        ref: new Model(type: ProgramYearObjectiveDTO::class),
                        type: 'object'
                    )
                ],
                type: 'object',
            )
        ),
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path')
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'The updated program year objective.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'programYearObjective',
                            ref: new Model(type: ProgramYearObjectiveDTO::class)
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '201',
                description: 'The newly created program year objective.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'programYearObjective',
                            ref: new Model(type: ProgramYearObjectiveDTO::class)
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: '400', description: 'Bad Request Data.'),
            new OA\Response(response: '403', description: 'Access Denied.'),
            new OA\Response(response: '404', description: 'Not Found.')
        ]
    )]
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
        path: '/api/{version}/programyearobjectives/{id}',
        summary: 'Delete a program year objective.',
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path')
        ],
        responses: [
            new OA\Response(response: '204', description: 'Deleted.'),
            new OA\Response(response: '403', description: 'Access Denied.'),
            new OA\Response(response: '404', description: 'Not Found.'),
            new OA\Response(
                response: '500',
                description: 'Deletion failed (usually caused by non-cascading relationships).'
            )
        ]
    )]
    public function delete(
        string $version,
        string $id,
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        return $this->handleDelete($version, $id, $authorizationChecker);
    }
}
