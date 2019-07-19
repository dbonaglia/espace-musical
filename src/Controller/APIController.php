<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class APIController extends AbstractController {

    public static function responseJson($data, $statut) {
        $response = new JsonResponse($data, $statut);
        $response->setContent($data);
        return $response;
    }

    public static function DateFormater(string $date) {
        $datetime = new \Datetime();
        $explodeDate = explode('/', $date);
        $datetime->setDate($explodeDate[2], $explodeDate[1], $explodeDate[0]);
        $datetime->setTime($explodeDate[3], $explodeDate[4]);
        return $datetime;
    }
}
