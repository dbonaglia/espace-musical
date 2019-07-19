<?php

namespace App\Controller;

use App\Entity\Instrument;
use App\Entity\Announcement;
use App\Controller\APIController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/api-db/announcements") */
class AnnouncementController extends AbstractController {

    /** @Route("/add",) */
    public function add(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        // On insère les données Json dans une nouvelle instance de l'entité Announcement
        $announcement = new Announcement();
        $announcement
            ->setTitle($data['title'])
            ->setContent($data['content'])
            ->setAuthor($ur->find($data['author']))
        ;
        if(array_key_exists('price', $data)) $announcement->setPrice($data['price']);

        // On vérifie les contraintes de validation
        $errors = $validator->validate($announcement);

        // On envoie la réponse après vérification des erreurs possible
        if(count($errors) > 0) {
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_I_AM_A_TEAPOT);
        } else {
            $manager->persist($announcement);
            $manager->flush();
            return new Response('L\'annonce à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
        }

    }
}
