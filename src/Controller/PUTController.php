<?php

namespace App\Controller;

use App\Controller\APIController;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/put", methods={"PUT"}) */
class PUTController extends AbstractController {

    // * Modifier un évènement
    /** @Route("/event") */
    public function putEvent(Request $request, ValidatorInterface $validator, ObjectManager $manager, EventRepository $er, SerializerInterface $serializer) {
        
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);
        
        // On vérifie que l'évènement existe bel et bien en base de données
        if ($event = $er->find($data['eventId'])) {
            // On vérifie que l'utilisateur connecté est bien celui qui a publié l'évènement
            if($data['connectedUserId'] == $event->getAuthor()->getId()) {
                $hasModifs = false;

                $hasModifs .= (APIController::insertInDB('type', $data, $event, 'Le type de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                $hasModifs .= (APIController::insertInDB('title', $data, $event, 'Le titre de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                $hasModifs .= (APIController::insertInDB('artists', $data, $event, 'Les artistes de l\'évènement sont identiques aux précédents.') === true) ? true : false;
                $hasModifs .= (APIController::insertInDB('location', $data, $event, 'Le lieu de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                $hasModifs .= (APIController::insertInDB('description', $data, $event, 'La description de l\'évènement est identique à l\'ancienne.') === true) ? true : false;
                $hasModifs .= (APIController::insertInDB('price', $data, $event, 'Le prix de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                
                if(array_key_exists('startDate', $data)) {
                    $startDate = APIController::DateFormater($data['startDate']);
                    if($startDate != $event->getStartDate($startDate)) {
                        $event->setStartDate();
                        $hasModifs = true;
                    } else {
                        return new Response('La date de début de l\'évènement est identique à l\'ancienne.', Response::HTTP_PRECONDITION_FAILED);
                    }
                }
                
                if(array_key_exists('endDate', $data)) {
                    $endDate = APIController::DateFormater($data['startDate']);
                    if($endDate != $event->getEndDate()) {
                        $event->setEndDate($endDate);
                        $hasModifs = true;
                    } else {
                        return new Response('La date de fin de l\'évènement est identique à l\'ancienne.', Response::HTTP_PRECONDITION_FAILED);
                    }
                }

                // On vérifie les contraintes de validation
                $errors = $validator->validate($event);
                
                // On envoie la réponse après vérification des erreurs possible
                if (count($errors) > 0) {
                    return new Response($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
                } elseif ($hasModifs) {
                    $event->setUpdatedAt(new \Datetime());
                    $manager->persist($event);
                    $manager->flush();
                    return new Response('L\'évènement à correctement été modifié.', Response::HTTP_OK);
                } elseif (!$hasModifs) {
                    return new Response('Vous n\'avez rien modifié', Response::HTTP_OK);
                }
            } else {
                return new Response('Seul l\'auteur de l\'évènement est autorisé à modifier celui-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('L\'évènement n\'a pas été trouvé en base de données.', Response::HTTP_NOT_FOUND);
        }
    }
}
