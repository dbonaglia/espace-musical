<?php

namespace App\Controller;

use App\Entity\Disk;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/** @Route("/api-db/disks") */
class DiskController extends AbstractController {

    /** @Route("/add") */
    public function add(Request $request, ValidatorInterface $validator, ObjectManager $manager) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        // On insère les données Json dans une nouvelle instance de l'entité Disk
        $disk = new Disk();
        $disk
            ->setName($data['name'])
            ->setArtist($data['artist'])
            ->setFormat($data['format'])
            ->setType($data['type'])
        ;

        // On vérifie les contraintes de validation
        $errors = $validator->validate($disk);

        // On envoie la réponse après vérification des erreurs possible
        if(count($errors) > 0) {
            return new Response($errors, Response::HTTP_I_AM_A_TEAPOT);
        } else {
            $manager->persist($disk);
            $manager->flush();
            return new Response('Le disque à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
        }
    }
}
