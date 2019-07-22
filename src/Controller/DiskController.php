<?php

namespace App\Controller;

use App\Entity\Disk;
use App\Controller\APIController;
use App\Repository\DiskRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/** @Route("/api-db/disks") */
class DiskController extends AbstractController {

    /** @Route("/add") */
    public function add(Request $request, ValidatorInterface $validator, ObjectManager $manager, UserRepository $ur, DiskRepository $dr) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $user = $ur->find($data['userid']);

        // Si le disk que veut rajoute l'utilisateur n'est pas déjà présent en base de données, on l'ajoute
        if(!$dr->findOneDisk($data['artist'], $data['name'])) {
            $disk = new Disk();
            $disk
                ->setArtist($data['artist'])
                ->setName($data['name'])
                ->setFormat($data['format'])
                ->setType($data['type'])
            ;

            // On vérifie les contraintes de validation
            $errors = $validator->validate($disk);

            // On envoie la réponse après vérification des erreurs possible
            if(count($errors) > 0) {
                return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_I_AM_A_TEAPOT);
            } else {
                $manager->persist($disk);
                $manager->flush();
            }
        }

        $user
            ->addDisk($dr->findOneDisk($data['artist'], $data['name'])[0])
            ->setUpdatedAt(new \Datetime())
        ;
        $manager->persist($user);
        $manager->flush();

        return new Response('Le disque à correctement été ajouté à la collection de l\'utilisateur', Response::HTTP_OK);
    }
}
