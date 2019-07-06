<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class APIController extends AbstractController {

    /** @Route("/responseJson", name="responseJson") */
    public function responseJson(Request $request) {
        $response = new Response();
        $response->setContent($request->query->get('json'));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
