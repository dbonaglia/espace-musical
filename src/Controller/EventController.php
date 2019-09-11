<?php

namespace App\Controller;

use App\Entity\Event;
use App\Controller\APIController;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/api-db/events") */
class EventController extends AbstractController {

    /** @Route("/get") */
    public function getAll(EventRepository $er, SerializerInterface $serializer) {
        $allEventsFromDB = $er->findAll();
        return APIController::responseJson($serializer->serialize($allEventsFromDB, 'json', ['groups' => 'event']), Response::HTTP_OK);
    }

    /** @Route("/add") */
    public function add(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur, SerializerInterface $serializer) {
        
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $event = new Event();

        // Formatage des dates
        $startDate = APIController::DateFormater($data['startDate']);
        $endDate = APIController::DateFormater($data['endDate']);

        // if($startDate > $endDate) return new Response('Votre date de fin d\'évènement ne peut être antérieure à votre date de début.', Response::HTTP_PRECONDITION_FAILED);
        
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
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
        } else {
            $manager->persist($event);
            $manager->flush();
            return APIController::responseJson($serializer->serialize($event, 'json', ['groups' => 'event']), Response::HTTP_CREATED);
        }
    }

    /** @Route("/edit") */
    public function edit(Request $request, ValidatorInterface $validator, ObjectManager $manager, EventRepository $er, SerializerInterface $serializer) {
        
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);
        
        $event = $er->find($data['eventId']);
        
        // On vérifie que l'évènement existe bel et bien en base de données
        if ($event) {
            // On vérifie que l'utilisateur connecté est bien celui qui a publié l'évènement
            if($data['connectedUserId'] == $event->getAuthor()->getId()) {
                $modifs = false;

                $modifs = (APIController::insertInDB('type', $data, $event, 'Le type de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                $modifs = (APIController::insertInDB('title', $data, $event, 'Le titre de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                $modifs = (APIController::insertInDB('artistes', $data, $event, 'Les artistes de l\'évènement sont identiques aux précédents.') === true) ? true : false;
                $modifs = (APIController::insertInDB('location', $data, $event, 'Le lieu de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                $modifs = (APIController::insertInDB('description', $data, $event, 'La description de l\'évènement est identique à l\'ancienne.') === true) ? true : false;
                $modifs = (APIController::insertInDB('price', $data, $event, 'Le prix de l\'évènement est identique à l\'ancien.') === true) ? true : false;
                
                if(array_key_exists('startDate', $data)) {
                    if($data['startDate'] != $event->getStartDate()) {
                        $startDate = APIController::DateFormater($data['startDate']);
                        $event->setStartDate($startDate);
                        $modifs = true;
                    } else {
                        return new Response('La date de début de l\'évènement est identique à l\'ancienne.', Response::HTTP_OK);
                    }
                }
                
                if(array_key_exists('endDate', $data)) {
                    if($data['endDate'] != $event->getEndDate()) {
                        $endDate = APIController::DateFormater($data['endDate']);
                        $event->setEndDate($endDate);
                        $modifs = true;
                    } else {
                        return new Response('La date de fin de l\'évènement est identique à l\'ancienne.', Response::HTTP_OK);
                    }
                }
                
                // On vérifie les contraintes de validation
                $errors = $validator->validate($event);
                
                // On envoie la réponse après vérification des erreurs possible
                if (count($errors) > 0) {
                    return new Response($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
                } elseif ($modifs) {
                    $event->setUpdatedAt(new \Datetime());
                    $manager->persist($event);
                    $manager->flush();
                    return new Response('L\'évènement à correctement été modifié.', Response::HTTP_OK);
                } elseif (!$modifs) {
                    return new Response('Vous n\'avez rien modifié', Response::HTTP_OK);
                }
            } else {
                return new Response('Seul l\'auteur de l\'évènement est autorisé à modifier celui-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('L\'évènement n\'a pas été trouvé en base de données.', Response::HTTP_NOT_FOUND);
        }
    }

    /** @Route("/delete") */
    public function delete(Request $request, ObjectManager $manager, EventRepository $er) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $event = $er->find($data['eventId']);

        if ($event) {
            if ($data['connectedUserId'] == $event->getAuthor()->getId()) {
                $manager->remove($event);
                $manager->flush();
                return new Response('L\'évènement à correctement été supprimé.', Response::HTTP_OK);
            } else {
                return new Response('Seul l\'auteur de l\'évènement est autorisé à supprimer celui-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('L\'évènement n\'a pas été trouvé en base de données.', Response::HTTP_NOT_FOUND);
        }
    }
}
