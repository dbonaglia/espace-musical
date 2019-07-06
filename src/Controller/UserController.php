<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/** @Route("/api-db/users") */
class UserController extends AbstractController {

    /** @Route("/add") */
    public function add(Request $request, UserPasswordEncoderInterface $encoder, SerializerInterface $serializer, ValidatorInterface $validator, ObjectManager $manager) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        // Avant toute chose, on vérifie que le mot de passe est assez long
        if(strlen($data['password']) < 8) return new Response('Votre mot de passe doit contenir 8 caractères minimum', Response::HTTP_I_AM_A_TEAPOT);

        // On insère les données Json dans une nouvelle instance de l'entité User
        $user = new User();
        $user
            ->setEmail($data['email'])
            ->setUsername($data['username'])
            ->setPassword($encoder->encodePassword($user, $data['password']))
        ;

        // On vérifie les contraintes de validation
        $errors = $validator->validate($user);

        // On envoie la réponse après vérification des erreurs possible
        if(count($errors) > 0) {
            return new Response($errors, Response::HTTP_I_AM_A_TEAPOT);
        } else {
            $manager->persist($user);
            $manager->flush();
            return new Response('L\'utilisateur à correctement été ajouté dans la base de données', Response::HTTP_CREATED);
        }
    }
}
