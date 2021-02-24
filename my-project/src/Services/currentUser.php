<?php
/**
 * Created by PhpStorm.
 * User: arnau
 * Date: 26/04/2019
 * Time: 09:28
 */

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;


class currentUser extends UserRepository
{

    private $securityObj;

    /**
     * NewMessages constructor.
     * @param ManagerRegistry $registry
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(ManagerRegistry $registry,\Symfony\Component\Security\Core\Security $security) // Definit ici la valeur de la globale
    {
        parent::__construct($registry, User::class);
        $this->securityObj = $security;
    }

    public function getCurrentUser()
    {
      return $this->securityObj->getUser()->getUsername();

    }
}