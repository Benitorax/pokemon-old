<?php

namespace App\Repository;

use App\Entity\BattleTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method BattleTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method BattleTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method BattleTeam[]    findAll()
 * @method BattleTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BattleTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BattleTeam::class);
    }

    // /**
    //  * @return BattleTeam[] Returns an array of BattleTeam objects
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
    public function findOneBySomeField($value): ?BattleTeam
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findOneByTrainer(UserInterface $user): ?BattleTeam
    {
        return $this->createQueryBuilder('b')
        ->andWhere('b.trainer = :user')
        ->setParameter('user', $user)
        ->leftJoin('b.trainer', 'u')
        ->addSelect('u')
        ->leftJoin('b.pokemons', 'p')
        ->addSelect('p')
        ->getQuery()
        ->getOneOrNullResult()
        ;
    }
}
