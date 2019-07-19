<?php

namespace App\Controller;

use App\Entity\Event;
use App\Controller\APIController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/api-db/events") */
class EventController extends AbstractController {

    /** @Route("/add") */
    public function add(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $event = new Event();

        // Formatage des dates
        $startDate = APIController::DateFormater($data['startDate']);
        $endDate = APIController::DateFormater($data['endDate']);
        if($startDate > $endDate) return new Response('Votre date de fin d\'évènement ne peut être antérieure à votre date de début.', Response::HTTP_I_AM_A_TEAPOT);

        $event
            ->setType($data['type'])
            ->setTitle($data['title'])
            ->setArtists($data['artists'])
            ->setLocation($data['location'])
            ->setDescription($data['description'])
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setPrice($data['price'])
            ->setAuthor($ur->find($data['author']))
        ;

        // On vérifie les contraintes de validation
        $errors = $validator->validate($event);

        // On envoie la réponse après vérification des erreurs possible
        if(count($errors) > 0) {
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_I_AM_A_TEAPOT);
        } else {
            $manager->persist($event);
            $manager->flush();
            return new Response('L\'évènement à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
        }
    }
}
