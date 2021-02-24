<?php
/**
 * Created by PhpStorm.
 * User: arnau
 * Date: 26/04/2019
 * Time: 09:28
 */

namespace App\Services;

use App\Entity\Messages;
use App\Repository\MessagesRepository;
use Doctrine\Persistence\ManagerRegistry;


class NewMessages extends MessagesRepository
{
    public $val;
    private $securityObj;

    /**
     * NewMessages constructor.
     * @param RegistryInterface $registry
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(ManagerRegistry $registry,\Symfony\Component\Security\Core\Security $security) // Definit ici la valeur de la globale
    {
        parent::__construct($registry, Messages::class);
        $this->securityObj = $security;
    }

    public function getCountMessages()
    {
        $user = $this->securityObj->getUser();
        return  $this->CountUnreadCount($user) ;
    }
}