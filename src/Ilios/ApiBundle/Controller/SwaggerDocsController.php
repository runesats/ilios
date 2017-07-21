<?php

namespace Ilios\ApiBundle\Controller;

use Ilios\ApiBundle\Service\SwaggerDocBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class SwaggerDocsController
 *
 * Produce the YAML files that document our endpoints
 *
 * @package Ilios\ApiBundle\Controller
 */
class SwaggerDocsController extends AbstractController
{
    /**
     * @var SwaggerDocBuilder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $kernelRootDir;

    /**
     * SwaggerDocsController constructor.
     * @param SwaggerDocBuilder $builder
     * @param string $kernelRootDir
     */
    public function __construct(SwaggerDocBuilder $builder, $kernelRootDir)
    {
        $this->builder = $builder;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Get a single YAML file which documents our endpoints
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $yamlRoute = $this->generateUrl(
            'ilios_swagger_file',
            [],
            UrlGenerator::ABSOLUTE_URL
        );
        return $this->render('@IliosApi/swagger/index.html.twig', array('yamlRoute' => $yamlRoute));
    }

    /**
     * Fetch the swagger-ui from vendor and send its contents as the response
     *
     * @param Request $request
     * @return Response
     */
    public function uiAction(Request $request, $fileName)
    {
        $fileName = empty($fileName)?'index.html':$fileName;
        $swaggerDistDir = $this->kernelRootDir . '/../vendor/swagger-api/swagger-ui/dist';
        $filePath = "${swaggerDistDir}/${fileName}";

        if (!is_readable($filePath)) {
            throw new NotFoundHttpException("${fileName} can't be found");
        }

        return new Response(file_get_contents($filePath));
    }

    /**
     * Get a single YAML file which documents our endpoints
     *
     * @param Request $request
     * @return Response
     */
    public function yamlAction(Request $request)
    {
        $yaml = $this->builder->getDocs($request);

        $response = new Response(
            $yaml,
            Response::HTTP_OK,
            ['Content-type' => 'application/x-yaml']
        );

        return $response;
    }
}
