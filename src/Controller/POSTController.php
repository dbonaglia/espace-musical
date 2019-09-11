<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class POSTController extends AbstractController
{
    /**
     * @Route("/p/o/s/t", name="p_o_s_t")
     */
    public function index()
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'POSTController',
        ]);
    }
}
