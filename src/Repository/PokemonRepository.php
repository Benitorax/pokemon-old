<?php

namespace App\Repository;

use App\Entity\Pokemon;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    // /**
    //  * @return Pokemon[] Returns an array of Pokemon objects
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
    public function findOneBySomeField($value): ?Pokemon
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findPokemonsByTrainer(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.trainer = :val')
            ->setParameter('val', $user)
            ->orderBy('p.name', 'ASC', 'p.level', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findReadyPokemonsByTrainer(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.trainer = :val')
            ->andWhere('p.isSleep = false')
            ->setParameter('val', $user)
            ->orderBy('p.name', 'ASC', 'p.level', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findReadyPokemonsByTrainerQueryBuilder(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.trainer = :val')
            ->andWhere('p.isSleep = false')
            ->setParameter('val', $user)
            ->orderBy('p.name', 'ASC', 'p.level', 'DESC')
        ;
    }

    public function findAllFullHPByTrainer(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.trainer = :val')
            ->andWhere('p.isSleep = false')
            ->andWhere('p.healthPoint = 100')
            ->setParameter('val', $user)
            ->orderBy('p.name', 'ASC', 'p.level', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllFullHPByTrainerQueryBuilder(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.trainer = :val')
            ->andWhere('p.isSleep = false')
            ->andWhere('p.healthPoint = 100')
            ->setParameter('val', $user)
            ->orderBy('p.name', 'ASC', 'p.level', 'DESC')
        ;
    }

    public function findAllFullHPByTrainerNumber(UserInterface $user)
    {
        return $this->createQueryBuilder('p')
            ->select('count(p)')
            ->andWhere('p.trainer = :val')
            ->andWhere('p.isSleep = false')
            ->andWhere('p.healthPoint = 100')
            ->setParameter('val', $user)
            ->orderBy('p.name', 'ASC', 'p.level', 'DESC')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
