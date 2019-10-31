<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\UserSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find all the user with the search input
     * @param UserSearch $search
     * @return Query
     */
    public function findAllUser(UserSearch $search)
    {

        $query = $this->createQueryBuilder('p');

        if ($search->getname()) { #if the user has enter something in the input search

            $query = $this->createQueryBuilder('p');
            $query = $query->where('p.username LIKE :search')

                #Put some joker for more results
                ->setParameter('search', '%' . $search->getname() . '%');
        }
        return $query->getQuery();
    }

    /**
     * Find all the users except the current user logged in
     * @param $me
     * @return Query
     */
    public function findUsersWithoutMe($me)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.id != :me') #without the current user logged in
            ->setParameter('me', $me)
            ->getQuery()
            ->getResult();

        return $query;

    }

}
