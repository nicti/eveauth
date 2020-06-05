<?php

namespace App\Repository;

use App\Entity\DiscordRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiscordRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscordRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscordRole[]    findAll()
 * @method DiscordRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscordRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscordRole::class);
    }

    // /**
    //  * @return DiscordRole[] Returns an array of DiscordRole objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DiscordRole
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
