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
use Symfony\Bridge\Doctrine\RegistryInterface;


class currentUser extends UserRepository
{

    private $securityObj;

    /**
     * NewMessages constructor.
     * @param RegistryInterface $registry
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(RegistryInterface $registry,\Symfony\Component\Security\Core\Security $security) // Definit ici la valeur de la globale
    {
        parent::__construct($registry, User::class);
        $this->securityObj = $security;
    }

    public function getCurrentUser()
    {
      return $this->securityObj->getUser()->getUsername();

    }
}