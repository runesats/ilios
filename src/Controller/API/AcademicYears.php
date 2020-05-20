<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Classes\AcademicYear;
use App\Entity\Manager\CourseManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/{version<v1|v2>}/academicyears")
 */
class AcademicYears
{
    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function getOne(
        string $version,
        string $id,
        CourseManager $courseManager,
        SerializerInterface $serializer
    ): Response {
        $years = $courseManager->getYears();

        if (!in_array($id, $years)) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return new Response(
            $serializer->serialize(
                [ 'academicYears' => [new AcademicYear($id)]],
                'json'
            ),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );
    }

    /**
     * @Route("/", methods={"GET"})
     */
    public function getAll(
        string $version,
        CourseManager $courseManager,
        SerializerInterface $serializer
    ): Response {
        $years = array_map(function ($year) {
            return new AcademicYear($year);
        }, $courseManager->getYears());

        return new Response(
            $serializer->serialize(
                [ 'academicYears' => $years],
                'json'
            ),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );
    }
}
