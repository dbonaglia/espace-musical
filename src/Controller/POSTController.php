<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Announcement;
use App\Controller\APIController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/post", methods={"POST"}) */
class POSTController extends AbstractController {

    // * Ajout d'un évènement
    /** @Route("/event") */
    public function createEvent(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur, SerializerInterface $serializer) {
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);
        
        // Formatage des dates
        $startDate = APIController::DateFormater($data['startDate']);
        $endDate = APIController::DateFormater($data['endDate']);
        
        $event = new Event();
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
        if (count($errors) > 0) {
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
        } else {
            $manager->persist($event);
            $manager->flush();
            return APIController::responseJson($serializer->serialize($event, 'json', ['groups' => 'event']), Response::HTTP_CREATED);
        }
    }

    // * Ajout d'une annonce
    /** @Route("/announcement") */
    public function createAnnouncement(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur, SerializerInterface $serializer) {
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        // On insère les données Json dans une nouvelle instance de l'entité Announcement
        $announcement = new Announcement();
        $announcement
            ->setTitle($data['title'])
            ->setContent($data['content'])
            ->setAuthor($ur->find($data['author']))
        ;
        // On défini un prix uniquement si l'utilisateur en a renseigné un
        if (array_key_exists('price', $data)) $announcement->setPrice($data['price']);

        // On vérifie les contraintes de validation
        $errors = $validator->validate($announcement);

        // On envoie la réponse après vérification des erreurs possible
        if (count($errors) > 0) {
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
        } else {
            $manager->persist($announcement);
            $manager->flush();
            return APIController::responseJson($serializer->serialize($announcement, 'json', ['groups' => 'announcement']), Response::HTTP_CREATED);
        }
    }
}
