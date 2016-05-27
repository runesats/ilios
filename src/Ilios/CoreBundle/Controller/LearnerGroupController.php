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
use Ilios\CoreBundle\Entity\LearnerGroupInterface;

/**
 * Class LearnerGroupController
 * @package Ilios\CoreBundle\Controller
 * @RouteResource("LearnerGroups")
 */
class LearnerGroupController extends FOSRestController
{
    /**
     * Get a LearnerGroup
     *
     * @ApiDoc(
     *   section = "LearnerGroup",
     *   description = "Get a LearnerGroup.",
     *   resource = true,
     *   requirements={
     *     {
     *        "name"="id",
     *        "dataType"="integer",
     *        "requirement"="\d+",
     *        "description"="LearnerGroup identifier."
     *     }
     *   },
     *   output="Ilios\CoreBundle\Entity\LearnerGroup",
     *   statusCodes={
     *     200 = "LearnerGroup.",
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
        $manager = $this->container->get('ilioscore.learnergroup.manager');
        $learnerGroup = $manager->findDTOBy(['id' => $id]);

        if (!$learnerGroup) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('view', $learnerGroup)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        $answer['learnerGroups'][] = $learnerGroup;

        return $answer;
    }

    /**
     * Get all LearnerGroup.
     *
     * @ApiDoc(
     *   section = "LearnerGroup",
     *   description = "Get all LearnerGroup.",
     *   resource = true,
     *   output="Ilios\CoreBundle\Entity\LearnerGroup",
     *   statusCodes = {
     *     200 = "List of all LearnerGroup",
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

        $manager = $this->container->get('ilioscore.learnergroup.manager');
        $result = $manager->findDTOsBy($criteria, $orderBy, $limit, $offset);

        $authChecker = $this->get('security.authorization_checker');
        $result = array_filter($result, function ($entity) use ($authChecker) {
            return $authChecker->isGranted('view', $entity);
        });

        //If there are no matches return an empty array
        $answer['learnerGroups'] =
            $result ? array_values($result) : [];

        return $answer;
    }

    /**
     * Create a LearnerGroup.
     *
     * @ApiDoc(
     *   section = "LearnerGroup",
     *   description = "Create a LearnerGroup.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\LearnerGroupType",
     *   output="Ilios\CoreBundle\Entity\LearnerGroup",
     *   statusCodes={
     *     201 = "Created LearnerGroup.",
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
            $handler = $this->container->get('ilioscore.learnergroup.handler');
            $learnerGroup = $handler->post($this->getPostData($request));

            $authChecker = $this->get('security.authorization_checker');
            if (! $authChecker->isGranted('create', $learnerGroup)) {
                throw $this->createAccessDeniedException('Unauthorized access!');
            }

            $manager = $this->container->get('ilioscore.learnergroup.manager');
            $manager->update($learnerGroup, true, false);

            $answer['learnerGroups'] = [$learnerGroup];

            $view = $this->view($answer, Codes::HTTP_CREATED);

            return $this->handleView($view);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update a LearnerGroup.
     *
     * @ApiDoc(
     *   section = "LearnerGroup",
     *   description = "Update a LearnerGroup entity.",
     *   resource = true,
     *   input="Ilios\CoreBundle\Form\Type\LearnerGroupType",
     *   output="Ilios\CoreBundle\Entity\LearnerGroup",
     *   statusCodes={
     *     200 = "Updated LearnerGroup.",
     *     201 = "Created LearnerGroup.",
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
            $manager = $this->container->get('ilioscore.learnergroup.manager');
            $learnerGroup = $manager->findOneBy(['id'=> $id]);
            if ($learnerGroup) {
                $code = Codes::HTTP_OK;
            } else {
                $learnerGroup = $manager->create();
                $code = Codes::HTTP_CREATED;
            }

            $handler = $this->container->get('ilioscore.learnergroup.handler');

            $learnerGroup = $handler->put($learnerGroup, $this->getPostData($request));

            $authChecker = $this->get('security.authorization_checker');
            if (! $authChecker->isGranted('edit', $learnerGroup)) {
                throw $this->createAccessDeniedException('Unauthorized access!');
            }

            $manager->update($learnerGroup, true, true);

            $answer['learnerGroup'] = $learnerGroup;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

        $view = $this->view($answer, $code);

        return $this->handleView($view);
    }

    /**
     * Delete a LearnerGroup.
     *
     * @ApiDoc(
     *   section = "LearnerGroup",
     *   description = "Delete a LearnerGroup entity.",
     *   resource = true,
     *   requirements={
     *     {
     *         "name" = "id",
     *         "dataType" = "integer",
     *         "requirement" = "\d+",
     *         "description" = "LearnerGroup identifier"
     *     }
     *   },
     *   statusCodes={
     *     204 = "No content. Successfully deleted LearnerGroup.",
     *     400 = "Bad Request.",
     *     404 = "Not found."
     *   }
     * )
     *
     * @Rest\View(statusCode=204)
     *
     * @param $id
     * @internal LearnerGroupInterface $learnerGroup
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $learnerGroup = $this->getOr404($id);

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('delete', $learnerGroup)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        try {
            $manager = $this->container->get('ilioscore.learnergroup.manager');
            $manager->delete($learnerGroup);

            return new Response('', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            throw new \RuntimeException("Deletion not allowed: " . $exception->getMessage());
        }
    }

    /**
     * Get a entity or throw a exception
     *
     * @param $id
     * @return LearnerGroupInterface $learnerGroup
     */
    protected function getOr404($id)
    {
        $manager = $this->container->get('ilioscore.learnergroup.manager');
        $learnerGroup = $manager->findOneBy(['id' => $id]);
        if (!$learnerGroup) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $learnerGroup;
    }

    /**
     * Parse the request for the form data
     *
     * @param Request $request
     * @return array
     */
    protected function getPostData(Request $request)
    {
        if ($request->request->has('learnerGroup')) {
            return $request->request->get('learnerGroup');
        }

        return $request->request->all();
    }
}
