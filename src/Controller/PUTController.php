<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PUTController extends AbstractController
{
    /**
     * @Route("/p/u/t", name="p_u_t")
     */
    public function index()
    {
        return $this->render('put/index.html.twig', [
            'controller_name' => 'PUTController',
        ]);
    }
}
