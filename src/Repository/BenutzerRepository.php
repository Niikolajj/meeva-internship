<?php

namespace App\Repository;

use App\Entity\Benutzer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Benutzer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Benutzer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Benutzer[]    findAll()
 * @method Benutzer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BenutzerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Benutzer::class);
    }

    // /**
    //  * @return Benutzer[] Returns an array of Benutzer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Benutzer
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
