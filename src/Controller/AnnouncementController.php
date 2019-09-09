<?php

namespace App\Controller;

use App\Entity\Instrument;
use App\Entity\Announcement;
use App\Controller\APIController;
use App\Repository\UserRepository;
use App\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/api-db/announcements") */
class AnnouncementController extends AbstractController {

    /** @Route("/add",) */
    public function add(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur, SerializerInterface $serializer) {

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
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
        } else {
            $manager->persist($announcement);
            $manager->flush();
            return new Response('L\'annonce à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
        }
    }

    /** @Route("/edit",) */
    public function edit(Request $request, ValidatorInterface $validator, ObjectManager $manager, AnnouncementRepository $ar, SerializerInterface $serializer) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $announcement = $ar->find($data['announcementId']);

        // On vérifie que l'annonce a bien été trouvée en base de données
        if($announcement) {
            // On vérifie que l'utilisateur connecté est bien celui qui a publié l'annonce
            if($data['connectedUserId'] == $announcement->getAuthor()->getId()) {
                $modifs = false;

                $modifs = (APIController::insertInDB('title', $data, $announcement, 'Le titre de l\'annonce est identique à l\'ancien.') === true) ? true : false;
                $modifs = (APIController::insertInDB('content', $data, $announcement, 'Le contenu de l\'annonce est identique à l\'ancien.') === true) ? true : false;
                $modifs = (APIController::insertInDB('price', $data, $announcement, 'Le prix de l\'annonce est identique à l\'ancien.') === true) ? true : false;

                // On vérifie les contraintes de validation
                $errors = $validator->validate($announcement);
                
                // On envoie la réponse après vérification des erreurs possible
                if (count($errors) > 0) {
                    return new Response($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
                } elseif ($modifs) {
                    $announcement->setUpdatedAt(new \Datetime());
                    $manager->persist($announcement);
                    $manager->flush();
                    return new Response('L\'annonce à correctement été modifiée.', Response::HTTP_OK);
                } elseif (!$modifs) {
                    return new Response('Vous n\'avez rien modifié', Response::HTTP_OK);
                }
            } else {
                return new Response('Seul l\'auteur de l\'annonce est autorisé à modifier celle-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('L\'annonce n\'a pas été trouvée en base de données.', Response::HTTP_NOT_FOUND);
        }
    }

    /** @Route("/delete") */
    public function delete(Request $request, ObjectManager $manager, AnnouncementRepository $ar) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $announcement = $ar->find($data['announcementId']);

        if ($announcement) {
            if ($data['connectedUserId'] == $announcement->getAuthor()->getId()) {
                $manager->remove($announcement);
                $manager->flush();
                return new Response('L\'annonce à correctement été supprimée.', Response::HTTP_OK);
            } else {
                return new Response('Seul l\'auteur de l\'annonce est autorisé à supprimer celle-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('L\'annonce n\'a pas été trouvée en base de données.', Response::HTTP_NOT_FOUND);
        }
    }
}
