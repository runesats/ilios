<?php

namespace Ilios\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function indexAction()
    {
        $file = $this->get('iliosweb.assets')->getIndex();
        if (!$file) {
            throw new \Exception('Unable to retrieve the index file');
        }
        $response = new Response($file);
        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }
}
