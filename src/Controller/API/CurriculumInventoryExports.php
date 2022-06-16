<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Classes\SessionUserInterface;
use App\Entity\CurriculumInventoryExportInterface;
use App\Entity\DTO\CurriculumInventoryExportDTO;
use App\Entity\UserInterface;
use App\RelationshipVoter\AbstractVoter;
use App\Repository\CurriculumInventoryExportRepository;
use App\Repository\UserRepository;
use App\Service\ApiRequestParser;
use App\Service\ApiResponseBuilder;
use App\Service\CurriculumInventory\Exporter;
use App\Traits\ApiEntityValidation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name:'Curriculum inventory exports')]
#[Route('/api/{version<v3>}/curriculuminventoryexports')]
class CurriculumInventoryExports
{
    use ApiEntityValidation;

    /**
     * Create the XML document for a curriculum inventory report
     */
    #[Route(methods: ['POST'])]
    #[OA\Post(
        path: '/api/{version}/curriculuminventoryexports',
        summary: "Create curriculum inventory exports.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        'curriculumInventoryExports',
                        ref: new Model(type: CurriculumInventoryExportDTO::class),
                        type: 'object'
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
                description: 'An array of newly created curriculum inventory exports.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            'curriculumInventoryExports',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: CurriculumInventoryExportDTO::class)
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
        CurriculumInventoryExportRepository $repository,
        ApiRequestParser $requestParser,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        ValidatorInterface $validator,
        Exporter $exporter,
        ApiResponseBuilder $builder
    ): Response {
        $class = $repository->getClass() . '[]';
        $entities = $requestParser->extractEntitiesFromPostRequest($request, $class, 'curriculuminventoryexports');

        /** @var SessionUserInterface $sessionUser */
        $sessionUser = $tokenStorage->getToken()->getUser();
        /** @var UserInterface $user */
        $user = $userRepository->findOneBy(['id' => $sessionUser->getId()]);
        /** @var CurriculumInventoryExportInterface $export */
        foreach ($entities as $export) {
            $export->setCreatedBy($user);
            if (! $authorizationChecker->isGranted(AbstractVoter::CREATE, $export)) {
                throw new AccessDeniedException('Unauthorized access!');
            }

            // generate and set the report document
            $document = $exporter->getXmlReport($export->getReport());
            $export->setDocument($document);
            $this->validateEntity($export, $validator);

            $repository->update($export, false);
        }

        $repository->flush();

        $ids = array_map(fn($entity) => $entity->getId(), $entities);

        $dtos = $repository->findDTOsBy(['id' => $ids]);

        return $builder->buildResponseForPostRequest(
            'curriculuminventoryexports',
            $dtos,
            Response::HTTP_CREATED,
            $request
        );
    }
}
