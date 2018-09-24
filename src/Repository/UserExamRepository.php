<?php

namespace App\Repository;

use App\Entity\UserExam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserExam|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserExam|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserExam[]    findAll()
 * @method UserExam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserExamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserExam::class);
    }

//    /**
//     * @return UserExam[] Returns an array of UserExam objects
//     */
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
    public function findOneBySomeField($value): ?UserExam
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
