<?php

namespace App\Repository;

use App\Entity\Battle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Battle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Battle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Battle[]    findAll()
 * @method Battle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BattleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Battle::class);
    }

    // /**
    //  * @return Battle[] Returns an array of Battle objects
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
    public function findOneBySomeField($value): ?Battle
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findOneByTrainer(UserInterface $user): ?Battle
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.playerTeam', 'pt')
            ->addSelect('pt')
            ->andWhere('pt.trainer = :user')
            ->leftJoin('pt.pokemons', 'pp')
            ->addSelect('pp')
            ->leftJoin('pt.currentFighter', 'pf')
            ->addSelect('pf')
            ->setParameter('user', $user)
            ->leftJoin('b.opponentTeam', 'opt')
            ->addSelect('opt')
            ->leftJoin('opt.pokemons', 'opp')
            ->addSelect('opp')
            ->leftJoin('opt.currentFighter', 'opf')
            ->addSelect('opf')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
