<?php

namespace App\DataFixtures;

use App\Entity\Disk;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Instrument;
use App\Entity\Announcement;
use App\Controller\APIController;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture {

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager) {

        // Users
        for ($i=0; $i < 10; $i++) { 
            $user[$i] = new User();
            $user[$i]
                ->setUsername('User'.$i)
                ->setEmail('user'.$i.'@gmail.com')
                ->setPassword($this->encoder->encodePassword($user[$i], 'dadadada'))
            ;
            $manager->persist($user[$i]);
        }
        
        // Disks
        $formats = ['CD', 'K7', 'Vinyl', 'Dématérialisé'];
        $types = ['Album', 'EP', 'Single', 'Best-of'];
        for ($i=0; $i < 20; $i++) {
            shuffle($formats);
            shuffle($types);
            shuffle($user);
            $disk[$i] = new Disk();
            $disk[$i]
                ->setName('Disk '.$i)
                ->setArtist('Artist of Disk '.$i)
                ->setFormat($formats[1])
                ->setType($types[1])
                ->addUser($user[1])
            ;
            $manager->persist($disk[$i]);
        }

        // Instruments
        $instruments = ['Guitare', 'Piano', 'Batterie', 'Basse', 'Chant', 'Trompette', 'Triangle', 'Contrebasse', 'Saxophone', 'Violon', 'Clavier', 'Harmonica', 'Flûte'];
        for ($i=0; $i < count($instruments); $i++) {
            $instruments[$i] = new Instrument();
            $instruments[$i]
                ->setName($instruments[$i])
            ;
            $manager->persist($instruments[$i]);
        }

        // Events
        $artists = [
            'Lady Gaga',
            'Paramore',
            'Adèle',
            'Indochine',
            'Queen',
            'Eminem',
            'AC/DC',
            'Nickelback',
            'Metallica',
            'Kyo',
            'Kylie Minogue',
            'Snoop Dogg',
            'Hans Zimmer',
            'Gojira',
            'Alestorm',
            'Halestorm'
        ];
        for ($i=0; $i < 20; $i++) {
            shuffle($artists);
            shuffle($user);
            $event[$i] = new Event();
            $startDate = APIController::DateFormater('18/07/2019/20/00');
            $endDate = APIController::DateFormater('18/07/2019/23/00');
            if($startDate > $endDate) return new Response('Votre date de fin d\'évènement ne peut être antérieure à votre date de début.', Response::HTTP_I_AM_A_TEAPOT);
            $event[$i]
                ->setType('Concert')
                ->setTitle('Concert de '.$artists[1])
                ->setArtists($artists[1].', '.$artists[2])
                ->setLocation('75000 Paris L\'Olympia')
                ->setDescription('Ceci est une description')
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setPrice(mt_rand(20, 100) .'€')
                ->setAuthor($user[1])
            ;
            $manager->persist($event[$i]);
        }

        // Announcements
        for ($i=0; $i < count($instruments); $i++) {
            shuffle($instruments);
            shuffle($user);
            $announcement[$i] = new Announcement();
            $announcement[$i]
                ->setTitle('Je vends un(e) : '.$instruments[1]->getName())
                ->setContent('Super état, jamais servi. Super état, jamais servi. Super état, jamais servi. Super état, jamais servi. Super état, jamais servi. Super état, jamais servi. Super état, jamais servi. ')
                ->setAuthor($user[1])
            ;
            $manager->persist($announcement[$i]);
        }
        $manager->flush();
    }
}
