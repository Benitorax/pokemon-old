<?php

namespace App\Repository;

use App\Entity\Battle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
            ->leftJoin('b.playerTeam', 'playerTeam')
            ->addSelect('playerTeam')
            ->andWhere('playerTeam.trainer = :user')
            ->setParameter('user', $user)
            ->leftJoin('playerTeam.trainer', 'playerTrainer')
            ->addSelect('playerTrainer')
            ->leftJoin('playerTrainer.pokemons', 'userPokemons')
            ->addSelect('userPokemons')
            ->leftJoin('playerTeam.pokemons', 'playerPokemons')
            ->addSelect('playerPokemons')
            ->leftJoin('playerTeam.currentFighter', 'playerFighter')
            ->addSelect('playerFighter')
            ->leftJoin('b.opponentTeam', 'opponentTeam')
            ->addSelect('opponentTeam')
            ->leftJoin('opponentTeam.pokemons', 'opponentPokemons')
            ->addSelect('opponentPokemons')
            ->leftJoin('opponentTeam.currentFighter', 'opponentFighter')
            ->addSelect('opponentFighter')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
