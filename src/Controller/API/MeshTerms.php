<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\DTO\MeshTermDTO;
use App\Repository\MeshTermRepository;
use App\Service\ApiResponseBuilder;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[OA\Tag(name:'MeSH terms')]
#[Route('/api/{version<v3>}/meshterms')]
class MeshTerms extends AbstractApiController
{
    public function __construct(MeshTermRepository $repository)
    {
        parent::__construct($repository, 'meshterms');
    }

    #[Route(
        '/{id}',
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/api/{version}/meshterms/{id}',
        summary: 'Fetch a single MeSH term.',
        parameters: [
            new OA\Parameter(name: 'version', description: 'API Version', in: 'path'),
            new OA\Parameter(name: 'id', description: 'id', in: 'path')
        ]
    )]
    #[OA\Response(
        response: '200',
        description: 'A single MeSH term.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'meshTerms',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: MeshTermDTO::class)
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
        path: "/api/{version}/meshterms",
        summary: "Fetch all MeSH terms.",
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
        description: 'An array of MeSH terms.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    'meshTerms',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: MeshTermDTO::class)
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
}
