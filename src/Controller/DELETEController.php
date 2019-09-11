<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\EventRepository;
use App\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("/delete", methods={"DELETE"}) */
class DELETEController extends AbstractController {

    // * Suppression d'un évènement
    /** @Route("/event") */
    public function deleteEvent(EventRepository $er, ObjectManager $manager, Request $request, UserRepository $ur) {
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        if ($event = $er->find($data['eventId'])) {
            if ($event->getAuthor() == $ur->find($data['author'])) {
                $manager->remove($event);
                $manager->flush();
                return new Response('L\'évènement à correctement été supprimé de la base de données.', Response::HTTP_OK, ['content-type' => 'text/html']);
            } else {
                return new Response('Seul l\'auteur de l\'évènement est autorisé à supprimer celui-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('Aucun évènement possédant cet id n\'a été trouvé.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }

    // * Suppression d'une annonce
    /** @Route("/announcement") */
    public function deleteAnnouncement(AnnouncementRepository $ar, ObjectManager $manager, Request $request, UserRepository $ur) {
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        if ($announcement = $ar->find($data['announcementId'])) {
            if ($announcement->getAuthor() == $ur->find($data['author'])) {
                $manager->remove($announcement);
                $manager->flush();
                return new Response('L\'annonce à correctement été supprimée de la base de données.', Response::HTTP_OK, ['content-type' => 'text/html']);
            } else {
                return new Response('Seul l\'auteur de l\'annonce est autorisé à supprimer celle-ci.', Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new Response('Aucune annonce possédant cet id n\'a été trouvée.', Response::HTTP_NOT_FOUND, ['content-type' => 'text/html']);
        }
    }
}
