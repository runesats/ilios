<?php

namespace Ilios\CoreBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ilios\CoreBundle\Exception\InvalidFormException;
use Ilios\CoreBundle\Handler\UserRoleHandler;
use Ilios\CoreBundle\Entity\UserRoleInterface;

/**
 * UserRole controller.
 * @package Ilios\CoreBundle\Controller\;
 * @RouteResource("UserRole")
 */
class UserRoleController extends FOSRestController
{
    
    /**
     * Get a UserRole
     *
     * @ApiDoc(
     *   description = "Get a UserRole.",
     *   resource = true,
     *   requirements={
     *     {"name"="userRoleId", "dataType"="integer", "requirement"="", "description"="UserRole identifier."}
     *   },
     *   output="Ilios\CoreBundle\Entity\UserRole",
     *   statusCodes={
     *     200 = "UserRole.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function getAction(Request $request, $id)
    {
        $answer['userRole'] = $this->getOr404($id);

        return $answer;
    }

    /**
     * Get all UserRole.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get all UserRole.",
     *   output="Ilios\CoreBundle\Entity\UserRole",
     *   statusCodes = {
     *     200 = "List of all UserRole",
     *     204 = "No content. Nothing to list."
     *   }
     * )
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
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
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $orderBy = $paramFetcher->get('order_by');
        $criteria = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

        $answer['userRole'] =
            $this->getUserRoleHandler()->findUserRolesBy(
                $criteria,
                $orderBy,
                $limit,
                $offset
            );

        if ($answer['userRole']) {
            return $answer;
        }

        return new ArrayCollection([]);
    }

    /**
     * Create a UserRole.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Create a UserRole.",
     *   input="Ilios\CoreBundle\Form\UserRoleType",
     *   output="Ilios\CoreBundle\Entity\UserRole",
     *   statusCodes={
     *     201 = "Created UserRole.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        try {
            $new  =  $this->getUserRoleHandler()->post($request->request->all());
            $answer['userRole'] = $new;

            return $answer;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Update a UserRole.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Update a UserRole entity.",
     *   input="Ilios\CoreBundle\Form\UserRoleType",
     *   output="Ilios\CoreBundle\Entity\UserRole",
     *   statusCodes={
     *     200 = "Updated UserRole.",
     *     201 = "Created UserRole.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            if ($userRole = $this->getUserRoleHandler()->findUserRoleBy(['userRoleId'=> $id])) {
                $answer['userRole']= $this->getUserRoleHandler()->put($userRole, $request->request->all());
                $code = Codes::HTTP_OK;
            } else {
                $answer['userRole'] = $this->getUserRoleHandler()->post($request->request->all());
                $code = Codes::HTTP_CREATED;
            }
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }

        $view = $this->view($answer, $code);

        return $this->handleView($view);
    }

    /**
     * Partial Update to a UserRole.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Partial Update to a UserRole.",
     *   input="Ilios\CoreBundle\Form\UserRoleType",
     *   output="Ilios\CoreBundle\Entity\UserRole",
     *   requirements={
     *     {"name"="userRoleId", "dataType"="integer", "requirement"="", "description"="UserRole identifier."}
     *   },
     *   statusCodes={
     *     200 = "Updated UserRole.",
     *     400 = "Bad Request.",
     *     404 = "Not Found."
     *   }
     * )
     *
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function patchAction(Request $request, $id)
    {
        $answer['userRole'] = $this->getUserRoleHandler()->patch($this->getOr404($id), $request->request->all());

        return $answer;
    }

    /**
     * Delete a UserRole.
     *
     * @ApiDoc(
     *   description = "Delete a UserRole entity.",
     *   resource = true,
     *   requirements={
     *     {
     *         "name" = "userRoleId",
     *         "dataType" = "integer",
     *         "requirement" = "",
     *         "description" = "UserRole identifier"
     *     }
     *   },
     *   statusCodes={
     *     204 = "No content. Successfully deleted UserRole.",
     *     400 = "Bad Request.",
     *     404 = "Not found."
     *   }
     * )
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $id
     * @internal UserRoleInterface $userRole
     *
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        $userRole = $this->getOr404($id);
        try {
            $this->getUserRoleHandler()->deleteUserRole($userRole);

            return new Response('', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            throw new \RuntimeException("Deletion not allowed");
        }
    }

    /**
     * Get a entity or throw a exception
     *
     * @param $id
     * @return UserRoleInterface $entity
     */
    protected function getOr404($id)
    {
        if (!($entity = $this->getUserRoleHandler()->findUserRoleBy(['userRoleId' => $id]))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $entity;
    }

    /**
     * @return UserRoleHandler
     */
    public function getUserRoleHandler()
    {
        return $this->container->get('ilioscore.userrole.handler');
    }
}
