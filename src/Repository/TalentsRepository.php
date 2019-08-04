<?php

namespace App\Repository;

use App\Entity\Talents;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Talents|null find($id, $lockMode = null, $lockVersion = null)
 * @method Talents|null findOneBy(array $criteria, array $orderBy = null)
 * @method Talents[]    findAll()
 * @method Talents[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TalentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Talents::class);
    }

    // /**
    //  * @return Talents[] Returns an array of Talents objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Talents
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
