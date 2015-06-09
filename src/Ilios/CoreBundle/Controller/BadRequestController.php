<?php

namespace Ilios\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BadRequestController extends Controller
{
    /**
     * This is the catch-all action for the api.
     * It sends a 404 and dies.
     */
    public function indexAction()
    {
        throw $this->createNotFoundException();
    }
}
