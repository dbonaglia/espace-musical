<?php

namespace App\Repository;

use App\Entity\UserInstrument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserInstrument|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInstrument|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInstrument[]    findAll()
 * @method UserInstrument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserInstrumentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserInstrument::class);
    }

    // /**
    //  * @return UserInstrument[] Returns an array of UserInstrument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserInstrument
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
