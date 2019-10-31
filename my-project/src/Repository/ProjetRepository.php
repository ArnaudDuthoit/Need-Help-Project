<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\ProjetSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;


/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * @param ProjetSearch $search
     * @return Query
     */
    public function findAllActive(ProjetSearch $search)
    {
        $query = $this->createQueryBuilder('p');

        if ($search->getProjectname()) { #Put the input search in the query.
            $query = $query
                    ->where('p.title LIKE :search')
                    #Add some joker at the beginning and a the end for more results
                    ->setParameter('search', '%' . $search->getProjectname() . '%');
        }
        if ($search->getTags()->count() > 0) { #if the user select at least one tag

            foreach ($search->getTags() as $tag) {
                $query = $query
                    ->andWhere(":tag MEMBER OF p.tags")
                    ->setParameter("tag", $tag);
            }
        }
        return $query->getQuery();
    }

    /**
     * Get all the projects order by date and with a max result of 8
     * @return Projet[]
     */
    public function findLatest(): array
    {

        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the most liked project with a limit of 4 result
     * @return Projet[]
     */
    public function findMostLiked(): array
    {
        $dql = $this->getEntityManager()
            ->createQuery('SELECT  a,count(a.id) as HIDDEN d from App:PostLike a JOIN App:Projet b group by a.projet order by d DESC')
            ->setMaxResults(8)
            ->getResult();

        return $dql;
    }


    /**
     * Get all the projects order by date with no results limit
     * @return Projet[]
     */
    public function findAllLatest(): array
    {

        return $this->createQueryBuilder('p')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }


    /**
     * Get the most liked project with no result limit
     * @return Projet[]
     */
    public function topLiked(): array
    {

        $dql = $this->getEntityManager()
            ->createQuery('SELECT  a,count(a.id) as HIDDEN d from App:PostLike a JOIN App:Projet b group by a.projet order by d DESC')
            ->getResult();
        return $dql;

    }

}
