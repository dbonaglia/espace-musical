<?php

namespace App\Controller;

use App\Entity\Message;
use App\Controller\APIController;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("api-db/messages") */
class MessageController extends AbstractController {

    /** @Route("/send") */
    public function send(Request $request, UserRepository $ur, ValidatorInterface $validator, ObjectManager $manager, SerializerInterface $serializer) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $message = new Message();
        $message
            ->setContent($data['content'])
            ->setAuthor($ur->find($data['author']))
            ->setReceiver($ur->find($data['receiver']))
        ;

        // On vérifie les contraintes de validation
        $errors = $validator->validate($message);

        // On envoie la réponse après vérification des erreurs possible
        if(count($errors) > 0) {
            return APIController::responseJson($serializer->serialize($errors, 'json'), Response::HTTP_I_AM_A_TEAPOT);
        } else {
            $manager->persist($message);
            $manager->flush();
            return new Response('Le message à correctement été ajouté dans la base de données.', Response::HTTP_CREATED);
        }
    }

    /** @Route("/mySendedMessages") */
    public function mySendedMessages(Request $request, UserRepository $ur, SerializerInterface $serializer, MessageRepository $mr) {

        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);
        
        $mySendedMessages = $mr->getMySendedMessages($ur->find($data['userid']));
        return APIController::responseJson($serializer->serialize($mySendedMessages, 'json'), Response::HTTP_OK);
    }
    
    /** @Route("/myAddressedMessages") */
    public function myAddressedMessages(Request $request, UserRepository $ur, SerializerInterface $serializer, MessageRepository $mr) {
        
        // On récupère les données Json sous forme de tableau PHP
        $data = json_decode($request->getContent(), true);

        $myAddressedMessages = $mr->getMyAddressedMessages($ur->find($data['userid']));
        return APIController::responseJson($serializer->serialize($myAddressedMessages, 'json'), Response::HTTP_OK);
    }
}
