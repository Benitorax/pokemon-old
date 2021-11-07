<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
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
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllInactivated()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActivated = false')
            ->getQuery()
            ->getResult();
    }

    public function findOneIsActivatedByEmail($email)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->andWhere('u.isActivated = true')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllActivated()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.pokemons', 'p')
            ->addSelect('p')
            ->andWhere('u.isActivated = true')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAdmin()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllEmailAndUsername()
    {
        $resultArray =  $this->createQueryBuilder('u')
            ->select('u.email, u.username')
            ->getQuery()
            ->getScalarResult();

        $emailArray = [];
        $usernameArray = [];

        foreach ($resultArray as $result) {
            $emailArray[] = $result['email'];
            $usernameArray[] = $result['username'];
        }

        return [
            'email' => $emailArray,
            'username' => $usernameArray
        ];
    }
}
