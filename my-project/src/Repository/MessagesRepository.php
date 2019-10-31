<?php

namespace App\Repository;

use App\Entity\Messages;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Messages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Messages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Messages[]    findAll()
 * @method Messages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Messages::class);
    }

    /**
     * Get all the privates messages received and sent by the user
     * @param User $me
     * @param $to
     * @return mixed
     */
    public function getMessage(User $me, $to)
    {

        $conversations = $this->createQueryBuilder('m')
            ->andWhere(' (m.fromId = :fromUser and m.toId = :toUser) OR ( m.fromId = :toUser and m.toId = :fromUser )')
            ->setParameter('fromUser', $me)
            ->setParameter('toUser', $to)
            ->orderBy('m.createdAt', 'desc')
            ->getQuery()
            ->getResult();

        return $conversations;
    }


    /**
     * Get the number of unread messages that are destinate to the user GROUPED by user
     * We put the result in an array for an easier loop in it
     * @param $to
     * @return array
     */
    public function unreadCount($to)
    {

        $dql = $this->getEntityManager()
            ->createQuery('SELECT (a.fromId) as fromUser,
            count(a.id) as count from App:Messages a 
            where a.ReadAt is null and a.toId=:toUser 
            group by a.fromId')
            ->setParameter('toUser', $to)
            ->getResult(Query::HYDRATE_ARRAY);

        $tbl = array();

        foreach ($dql as $ar) {
            $uid = $ar['fromUser'];
            $tbl[$uid] = $ar['count'];
        }
        return $tbl;
    }

    /**
     * Get the total of unread messages
     * @param $to
     * @return array
     */
    public function CountUnreadCount($to)
    {
        $dql = $this->getEntityManager()
            ->createQuery('SELECT a.id from App:Messages a 
                                where a.ReadAt is null and a.toId=:toUser')
            ->setParameter('toUser', $to)
            ->getResult(Query::HYDRATE_ARRAY);
        return $dql;
    }


    /**
     * Set all the messages of the current conversation with a read_at date
     * @param $from
     * @param $to
     * @return mixed
     */
    public function readAllFrom($from, $to)
    {

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->update(Messages::class, 'm')
            ->set('m.ReadAt', ':now')
            ->Where('m.fromId = :fromUser')
            ->andWhere('m.toId = :toUser')
            ->setParameter('now', new \DateTime())
            ->setParameter('fromUser', $to)
            ->setParameter('toUser', $from);

        $query = $queryBuilder->getQuery();
        // echo $query->getDQL(), "\n";
        $query->execute();
        return $queryBuilder;
    }

}
