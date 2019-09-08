<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public static function insertInDB(string $key, array $data, object $class, string $sentence) {
        // On définit les Getters et Setters de manière dynamique en fonction de la clé passée en paramètre
        $setter = 'set'.ucfirst($key);
        $getter = 'get'.ucfirst($key);
        // On vérifie que la clé $key passée en paramètre fait partie du tableau $data passée en paramètre
        if(array_key_exists($key, $data)) {
            // On vérifie que la valeur fournie est différente que celle présente en base de données
            if($data[$key] != $class->$getter()) {
                $class->$setter($data[$key]);
                return true;
            } else {
                return new Response($sentence, Response::HTTP_PRECONDITION_FAILED);
            }
        }
    }
}
