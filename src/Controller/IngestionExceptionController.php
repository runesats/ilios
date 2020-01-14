<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class IngestionExceptionController
 * IngestionExceptions can only be GETed nothing else
 */
class IngestionExceptionController extends ApiController
{
    /**
     * Send a 404 header to the user
     */
    public function fourOhFourAction()
    {
        throw new NotFoundHttpException('Curriculum Inventory Exports can only be created');
    }
}
