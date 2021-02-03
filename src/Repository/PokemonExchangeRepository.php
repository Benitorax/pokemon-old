<?php

namespace App\Repository;

use App\Entity\PokemonExchange;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method ExchangePokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangePokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangePokemon[]    findAll()
 * @method ExchangePokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonExchangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonExchange::class);
    }

    // /**
    //  * @return ExchangePokemon[] Returns an array of ExchangePokemon objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExchangePokemon
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllByTrainer(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.trainer1 = :t1')
            ->setParameter('t1', $user)
            ->orWhere('p.trainer2 = :t2')
            ->setParameter('t2', $user)
            ->leftJoin('p.trainer1', 't1')
            ->addSelect('t1')
            ->leftJoin('p.trainer2', 't2')
            ->addSelect('t2')
            ->leftJoin('p.pokemon1', 'p1')
            ->addSelect('p1')
            ->leftJoin('p.pokemon2', 'p2')
            ->addSelect('p2')
            //->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
