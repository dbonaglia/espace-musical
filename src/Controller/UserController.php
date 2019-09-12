<?php

namespace App\Controller;

use App\Entity\User;
use App\Controller\APIController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/** @Route("/api-db/users") */
class UserController extends AbstractController {
    
  /** @Route("/get") */
  public function getAll(UserRepository $ur, SerializerInterface $serializer) {
    $allUsersFromDB = $ur->findAll();
    return APIController::responseJson($serializer->serialize($allUsersFromDB, 'json', ['groups' => 'user']), Response::HTTP_OK);
  }
  
  /** @Route("/add") */
  public function add(Request $request, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator, ObjectManager $manager, SerializerInterface $serializer) {

    // On récupère les données Json sous forme de tableau PHP
    $data = json_decode($request->getContent(), true);

    // On insère les données Json dans une nouvelle instance de l'entité User
    $user = new User();
    $user
      ->setEmail($data['email'])
      ->setUsername($data['username'])
      ->setPassword($data['password'])
    ;

    // On vérifie les contraintes de validation
    $errors = $validator->validate($user);
    
    // On envoie la réponse après vérification des erreurs possible
    if(count($errors) > 0) {
      return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
    } else {
      $user->setPassword($encoder->encodePassword($user, $data['password']));
      $manager->persist($user);
      $manager->flush();
      return new Response('L\'utilisateur à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
    }
  }

  /** @Route("/connect") */
  public function connect(Request $request, SerializerInterface $serializer) {
    if($user = $this->getUser()) {
      $user->setPassword(''); // Pour raison de sécurité, on évite de renvoyer le mdp au front
      return APIController::responseJson($serializer->serialize($user, 'json'), Response::HTTP_OK);
    }
  }

  /** @Route("/edit") */
  public function edit(Request $request, UserRepository $ur, UserPasswordEncoderInterface $encoder, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer) {

    // On récupère les données Json sous forme de tableau PHP
    $data = json_decode($request->getContent(), true);

    // Si un utilisateur avec cette adresse email est trouvé en BDD
    if($user = $ur->findOneByEmail($data['email'])) {

      // Si son mot de passe actuel est correct
      if(password_verify($data['password'], $user->getPassword())) {
        $modifs = false;
        $passwordModif = false;

        $modifs .= (APIController::insertInDB('username', $data, $user, 'Votre pseudo est identique à l\'ancien.') === true) ? true : false;
        $modifs .= (APIController::insertInDB('firstname', $data, $user, 'Votre prénom est identique à l\'ancien.') === true) ? true : false;
        $modifs .= (APIController::insertInDB('lastname', $data, $user, 'Votre nom est identique à l\'ancien.') === true) ? true : false;
        $modifs .= (APIController::insertInDB('zipCode', $data, $user, 'Votre code postal est identique à l\'ancien.') === true) ? true : false;
        $modifs .= (APIController::insertInDB('address', $data, $user, 'Votre adresse est identique à l\'ancienne.') === true) ? true : false;
        
        // S'il souhaite modifier son mot de passe
        if(array_key_exists('passwordNew', $data) || array_key_exists('passwordNewConfirm', $data)) {
          if($data['passwordNew'] === $data['passwordNewConfirm']) {
            if(!password_verify($data['passwordNew'], $user->getPassword())) {
              $user->setPassword($data['passwordNew']);
              $passwordModif = true;
              $modifs = true;
            } else {
              return new Response('Votre nouveau mot de passe est identique à l\'ancien.', Response::HTTP_PRECONDITION_FAILED);
            }
          } else {
            return new Response('Les nouveaux mots de passe ne correspondent pas.', Response::HTTP_PRECONDITION_FAILED);
          }
        }

        if(array_key_exists('emailNew', $data)) {
          if($data['emailNew'] != $user->getEmail()) {
            $user->setEmail($data['emailNew']);
            $modifs = true;
          } else {
            return new Response('Votre nouvelle adresse email est identique à l\'ancienne.', Response::HTTP_PRECONDITION_FAILED);
          }
        }
        
        // On vérifie les contraintes de validation
        $errors = $validator->validate($user);

        // On envoie la réponse après vérification des erreurs possible
        if(count($errors) > 0) {
          return new Response($serializer->serialize($errors, 'json'), Response::HTTP_PRECONDITION_FAILED);
        } elseif ($modifs) {
          if($passwordModif) $user->setPassword($encoder->encodePassword($user, $data['passwordNew']));
          $user->setUpdatedAt(new \Datetime());
          $manager->persist($user);
          $manager->flush();
          return new Response('L\'utilisateur à correctement été modifié.', Response::HTTP_OK);
        } elseif(!$modifs) {
          return new Response('Vous n\'avez rien modifié', Response::HTTP_OK);
        }
      } else {
        return new Response('Votre mot de passe n\'est pas correct.', Response::HTTP_UNAUTHORIZED);
      }
    } else {
      return new Response('Aucun utilisateur possédant cette adresse email.', Response::HTTP_UNAUTHORIZED);
    }
  }
}
