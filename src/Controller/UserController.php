<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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
    public function add(Request $request, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator, ObjectManager $manager) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        // Avant toute chose, on vérifie que le mot de passe est assez long
        if(strlen($data['password']) < 8) return new Response('Votre mot de passe doit contenir 8 caractères minimum.', Response::HTTP_I_AM_A_TEAPOT);
        
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
            return new Response('L\'utilisateur à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
        }
    }

    /** @Route("/connect") */
    public function connect(Request $request, SerializerInterface $serializer) {

        if($user = $this->getUser()) {
            $user->setPassword(''); // Pour raison de sécurité, on évite de renvoyer le mdp au front
            return $this->redirectToRoute('responseJson', ['json' => $serializer->serialize($user, 'json')]);
        }
    }

    /** @Route("/edit") */
    public function edit(Request $request, UserRepository $ur, UserPasswordEncoderInterface $encoder, ObjectManager $manager, ValidatorInterface $validator) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        // Si un utilisateur avec cette adresse emai lest trouvé en BDD
        if($user = $ur->findOneByEmail($data['email'])) {

            // Si son mot de passe actuel est correct
            if(password_verify($data['password'], $user->getPassword())) {
                $modifs = false;
                // S'il souhaite modifier son mot de passe
                if(array_key_exists('passwordNew', $data) || array_key_exists('passwordNewConfirm', $data)) {
                    if($data['passwordNew'] === $data['passwordNewConfirm']) {
                        $user->setPassword($encoder->encodePassword($user, $data['password']));
                        $modifs = true;
                    } else {
                        return new Response('Les nouveaux mot de passe ne correspondent pas.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }

                if(array_key_exists('emailnew', $data)) {
                    if($data['emailnew'] != $user->getEmail()) {
                        $user->setEmail($data['emailnew']);
                        $modifs = true;
                    } else {
                        return new Response('Votre nouvelle adresse email est identique à l\'ancienne.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }
                if(array_key_exists('username', $data)) {
                    if($data['username'] != $user->getUsername()) {
                        $user->setUsername($data['username']);
                        $modifs = true;
                    } else {
                        return new Response('Votre pseudo est identique à l\'ancien.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }
                if(array_key_exists('firstname', $data)) {
                    if($data['firstname'] != $user->getFirstname()) {
                        $user->setFirstname($data['firstname']);
                        $modifs = true;
                    } else {
                        return new Response('Votre prénom est identique à l\'ancien.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }
                if(array_key_exists('lastname', $data)) {
                    if($data['lastname'] != $user->getLastname()) {
                        $user->setLastname($data['lastname']);
                        $modifs = true;
                    } else {
                        return new Response('Votre nom est identique à l\'ancien.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }
                if(array_key_exists('zipCode', $data)) {
                    if($data['zipCode'] != $user->getZipCode()) {
                        $user->setZipCode($data['zipCode']);
                        $modifs = true;
                    } else {
                        return new Response('Votre code postal est identique à l\'ancien.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }
                if(array_key_exists('address', $data)) {
                    if($data['address'] != $user->getAddress()) {
                        $user->setAddress($data['address']);
                        $modifs = true;
                    } else {
                        return new Response('Votre adresse est identique à l\'ancienne.', Response::HTTP_I_AM_A_TEAPOT);
                    }
                }

                // On vérifie les contraintes de validation
                $errors = $validator->validate($user);

                // On envoie la réponse après vérification des erreurs possible
                if(count($errors) > 0) {
                    return new Response($errors, Response::HTTP_I_AM_A_TEAPOT);
                } else if ($modifs) {
                    $user->setUpdatedAt(new \Datetime());
                    $manager->persist($user);
                    $manager->flush();
                    return new Response('L\'utilisateur à correctement été modifié.', Response::HTTP_OK);
                } else {
                    return new Response('Vous n\'avez rien modifié', Response::HTTP_I_AM_A_TEAPOT);
                }
            } else {
                return new Response('Votre mot de passe n\'est pas corect', Response::HTTP_I_AM_A_TEAPOT);
            }
        } else {
            return new Response('Aucun utilisateur avec cette adresse email trouvé.', Response::HTTP_I_AM_A_TEAPOT);
        }
    }
}
