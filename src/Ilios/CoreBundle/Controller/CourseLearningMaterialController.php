<?php

namespace Ilios\CoreBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ilios\CoreBundle\Exception\InvalidFormException;
use Ilios\CoreBundle\Entity\CourseLearningMaterialInterface;

/**
 * Class CourseLearningMaterialController
 * @package Ilios\CoreBundle\Controller
 * @RouteResource("CourseLearningMaterials")
 */
class CourseLearningMaterialController extends FOSRestController
{
    /**
     * Get a CourseLearningMaterial
     *
     * @ApiDoc(
     *   section = "CourseLearningMaterial",
     *   description = "Get a CourseLearningMaterial.",
     *   resource = true,
     *   requirements={
     *     {
     *        "name"="id",
     *        "dataType"="integer",
     *        "requirement"="\d+",
     *        "description"="CourseLearningMaterial identifier."
     *     }
     *   },
     *   output="Ilios\CoreBundle\Entity\CourseLearningMaterial",
     *   statusCodes={
     *     200 = "CourseLearningMaterial.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        $courseLearningMaterial = $this->getOr404($id);

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('view', $courseLearningMaterial)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        $answer['courseLearningMaterials'][] = $courseLearningMaterial;

        return $answer;
    }

    /**
     * Get all CourseLearningMaterial.
     *
     * @ApiDoc(
     *   section = "CourseLearningMaterial",
     *   description = "Get all CourseLearningMaterial.",
     *   resource = true,
     *   output="Ilios\CoreBundle\Entity\CourseLearningMaterial",
     *   statusCodes = {
     *     200 = "List of all CourseLearningMaterial",
     *     204 = "No content. Nothing to list."
     *   }
     * )
     *
     * @QueryParam(
     *   name="offset",
     *   requirements="\d+",
     *   nullable=true,
     *   description="Offset from which to start listing notes."
     * )
     * @QueryParam(
     *   name="limit",
     *   requirements="\d+",
     *   default="20",
     *   description="How many notes to return."
     * )
     * @QueryParam(
     *   name="order_by",
     *   nullable=true,
     *   array=true,
     *   description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC"
     * )
     * @QueryParam(
     *   name="filters",
     *   nullable=true,
     *   array=true,
     *   description="Filter by fields. Must be an array ie. &filters[id]=3"
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $orderBy = $paramFetcher->get('order_by');
        $criteria = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : [];
        $criteria = array_map(function ($item) {
            $item = $item == 'null' ? null : $item;
            $item = $item == 'false' ? false : $item;
            $item = $item == 'true' ? true : $item;

            return $item;
        }, $criteria);
        $manager = $this->container->get('ilioscore.courselearningmaterial.manager');
        $result = $manager->findBy($criteria, $orderBy, $limit, $offset);

        $authChecker = $this->get('security.authorization_checker');
        $result = array_filter($result, function ($entity) use ($authChecker) {
            return $authChecker->isGranted('view', $entity);
        });

        //If there are no matches return an empty array
        $answer['courseLearningMaterials'] =
            $result ? array_values($result) : [];

        return $answer;
    }

    /**
     * Create a CourseLearningMaterial.
     *
     * @ApiDoc(
     *   section = "CourseLearningMaterial",
     *   description = "Create a CourseLearningMaterial.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\CourseLearningMaterialType",
     *   output="Ilios\CoreBundle\Entity\CourseLearningMaterial",
     *   statusCodes={
     *     201 = "Created CourseLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        try {
            $handler = $this->container->get('ilioscore.courselearningmaterial.handler');

            $courseLearningMaterial = $handler->post($this->getPostData($request));

            $authChecker = $this->get('security.authorization_checker');
            if (! $authChecker->isGranted('create', $courseLearningMaterial)) {
                throw $this->createAccessDeniedException('Unauthorized access!');
            }

            $manager = $this->container->get('ilioscore.courselearningmaterial.manager');
            $manager->update($courseLearningMaterial, true, false);

            $answer['courseLearningMaterials'] = [$courseLearningMaterial];

            $view = $this->view($answer, Codes::HTTP_CREATED);

            return $this->handleView($view);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update a CourseLearningMaterial.
     *
     * @ApiDoc(
     *   section = "CourseLearningMaterial",
     *   description = "Update a CourseLearningMaterial entity.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\CourseLearningMaterialType",
     *   output="Ilios\CoreBundle\Entity\CourseLearningMaterial",
     *   statusCodes={
     *     200 = "Updated CourseLearningMaterial.",
     *     201 = "Created CourseLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            $manager = $this->container->get('ilioscore.courselearningmaterial.manager');
            $courseLearningMaterial = $manager->findOneBy(['id'=> $id]);
            if ($courseLearningMaterial) {
                $code = Codes::HTTP_OK;
            } else {
                $courseLearningMaterial = $manager->create();
                $code = Codes::HTTP_CREATED;
            }

            $handler = $this->container->get('ilioscore.courselearningmaterial.handler');

            $courseLearningMaterial = $handler->put($courseLearningMaterial, $this->getPostData($request));

            $authChecker = $this->get('security.authorization_checker');
            if (! $authChecker->isGranted('edit', $courseLearningMaterial)) {
                throw $this->createAccessDeniedException('Unauthorized access!');
            }

            $manager->update($courseLearningMaterial, true, true);

            $answer['courseLearningMaterial'] = $courseLearningMaterial;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

        $view = $this->view($answer, $code);

        return $this->handleView($view);
    }

    /**
     * Delete a CourseLearningMaterial.
     *
     * @ApiDoc(
     *   section = "CourseLearningMaterial",
     *   description = "Delete a CourseLearningMaterial entity.",
     *   resource = true,
     *   requirements={
     *     {
     *         "name" = "id",
     *         "dataType" = "integer",
     *         "requirement" = "\d+",
     *         "description" = "CourseLearningMaterial identifier"
     *     }
     *   },
     *   statusCodes={
     *     204 = "No content. Successfully deleted CourseLearningMaterial.",
     *     400 = "Bad Request.",
     *     404 = "Not found."
     *   }
     * )
     *
     * @Rest\View(statusCode=204)
     *
     * @param $id
     * @internal CourseLearningMaterialInterface $courseLearningMaterial
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $courseLearningMaterial = $this->getOr404($id);

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('delete', $courseLearningMaterial)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        try {
            $manager = $this->container->get('ilioscore.courselearningmaterial.manager');
            $manager->delete($courseLearningMaterial);

            return new Response('', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            throw new \RuntimeException("Deletion not allowed: " . $exception->getMessage());
        }
    }

    /**
     * Get a entity or throw a exception
     *
     * @param $id
     * @return CourseLearningMaterialInterface $courseLearningMaterial
     */
    protected function getOr404($id)
    {
        $manager = $this->container->get('ilioscore.courselearningmaterial.manager');
        $courseLearningMaterial = $manager->findOneBy(['id' => $id]);
        if (!$courseLearningMaterial) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $courseLearningMaterial;
    }

    /**
     * Parse the request for the form data
     *
     * @param Request $request
     * @return array
     */
    protected function getPostData(Request $request)
    {
        if ($request->request->has('courseLearningMaterial')) {
            return $request->request->get('courseLearningMaterial');
        }

        return $request->request->all();
    }
}
