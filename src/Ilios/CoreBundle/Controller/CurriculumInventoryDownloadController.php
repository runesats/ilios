<?php

namespace Ilios\CoreBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Ilios\CoreBundle\Handler\CurriculumInventoryExportHandler;
use Ilios\CoreBundle\Entity\CurriculumInventoryReportInterface;
use Ilios\CoreBundle\Handler\CurriculumInventoryReportHandler;

/**
 * Class CurriculumInventoryDownloadController
 * @package Ilios\CoreBundle\Controller
 * @RouteResource("CurriculumInventoryDownloads")
 */
class CurriculumInventoryDownloadController extends FOSRestController
{
    /**
     * Downloads the curriculum inventory report document for a given report.
     *
     * @ApiDoc(
     *   section = "CurriculumInventoryDownload",
     *   description = "Downloads a curriculum inventory report document for a given report id.",
     *   resource = true,
     *   requirements={
     *     {
     *        "name"="id",
     *        "dataType"="integer",
     *        "requirement"="\d+",
     *        "description"="The curriculum inventory report identifier."
     *     }
     *   },
     *   output="string",
     *   statusCodes={
     *     200 = "OK",
     *     403 = "Access Denied.",
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
        $curriculumInventoryReport = $this->getCurriculumInventoryReportHandler()
            ->findCurriculumInventoryReportBy(['id' => $id]);

        if (! $curriculumInventoryReport) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        $authChecker = $this->get('security.authorization_checker');
        if (! $authChecker->isGranted('view', $curriculumInventoryReport)) {
            throw $this->createAccessDeniedException('Unauthorized access!');
        }

        $document = $this->getExportedDocument($curriculumInventoryReport);

        $response = new Response($document);
        $response->headers->set('Content-Type', 'application/xml; charset="utf-8"');
        $response->headers->set('Content-disposition', 'attachment; filename="report.xml"');
        return $response;

    }

    /**
     * Retrieves the report document for a given curriculum inventory report.
     *
     * @param CurriculumInventoryReportInterface $report
     * @return string
     */
    protected function getExportedDocument(CurriculumInventoryReportInterface $report)
    {
        // check if the report has been exported.
        // if so, pull the document from the database.
        $export = $this->getCurriculumInventoryExportHandler()->findCurriculumInventoryExportBy([
            'report' => $report->getId()
        ]);
        if ($export) {
            return $export->getDocument();
        }

        // otherwise, generate a document on the fly.
        return $this->generateReportDocument($report);
    }
    /**
     * @return CurriculumInventoryExportHandler
     */
    protected function getCurriculumInventoryExportHandler()
    {
        return $this->container->get('ilioscore.curriculuminventoryexport.handler');
    }

    /**
     * @return CurriculumInventoryReportHandler
     */
    protected function getCurriculumInventoryReportHandler()
    {
        return $this->container->get('ilioscore.curriculuminventoryreport.handler');
    }

    /**
     * Generates a report document on the fly for a given report
     *
     * @param CurriculumInventoryReportInterface $report
     * @return string the report document
     */
    protected function generateReportDocument(CurriculumInventoryReportInterface $report)
    {
        $xml = $this->container->get('ilioscore.curriculum_inventory.exporter')->getXmlReport($report);
        return $xml->saveXML();
    }
}
