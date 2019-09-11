<?php

namespace App\Controller;

use App\Controller\APIController;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/get", methods={"GET"}) */
class GETController extends AbstractController {

    // * Récupération de tous les évènements
    /** @Route("/events") */
    public function getEvents(EventRepository $er, SerializerInterface $serializer) {
        if ($events = $er->findAll()) {
            return APIController::responseJson($serializer->serialize($events, 'json', ['groups' => 'event']), Response::HTTP_FOUND);
        } else {
            return new Response('Aucun évènement en base de données.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }
    
    // * Récupération d'un évènement en particulier via son id
    /** @Route("/events/{id}") */
    public function getEvent($id, EventRepository $er, SerializerInterface $serializer) {
        if ($event->find($id)) {
            return APIController::responseJson($serializer->serialize($event, 'json', ['groups' => 'event']), Response::HTTP_FOUND);
        } else {
            return new Response('Aucun évènement possédant cet id n\'a été trouvé.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }

    // * Récupération de toutes les annonces
    /** @Route("/announcements") */
    public function getAnnouncements(AnnouncementRepository $ar, SerializerInterface $serializer) {
        if ($announcements = $ar->findAll()) {
            return APIController::responseJson($serializer->serialize($announcements, 'json', ['groups' => 'announcement']), Response::HTTP_OK);
        } else {
            return new Response('Aucune annonce en base de données.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }
    
    // * Récupération d'une annonce en particulier via son id
    /** @Route("/announcements/{id}") */
    public function getAnnouncement($id, AnnouncementRepository $ar, SerializerInterface $serializer) {
        if ($announcement = $ar->find($id)) {
            return APIController::responseJson($serializer->serialize($announcement, 'json', ['groups' => 'announcement']), Response::HTTP_FOUND);
        } else {
            return new Response('Aucune annonce possédant cet id n\'a été trouvée.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }

    // * Récupération de tous les utilisateurs
    /** @Route("/users") */
    public function getAllUsers(UserRepository $ur, SerializerInterface $serializer) {
        if ($users = $ur->findAll()) {
            return APIController::responseJson($serializer->serialize($users, 'json', ['groups' => 'user']), Response::HTTP_OK);
        } else {
            return new Response('Aucun utilisateur en base de données.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }
    
    // * Récupération d'un utilisateur en particulier via son id
    /** @Route("/users/{id}") */
    public function getOneUser($id, UserRepository $ur, SerializerInterface $serializer) {
        if ($user = $ur->find($id)) {
            return APIController::responseJson($serializer->serialize($user, 'json', ['groups' => 'user']), Response::HTTP_OK);
        } else {
            return new Response('Aucun utilisateur possédant cet id n\'a été trouvé.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }
}
