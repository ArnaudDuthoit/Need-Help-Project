<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Projet;

class EditProjetVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if(!$subject instanceof Projet){
            return self::ACCESS_ABSTAIN;
        }

        if(!in_array('EDIT',$attributes)){
            return self::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if(!$user instanceof User){
            return self::ACCESS_DENIED;
        }

        if($user !== $subject->getUser()){
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;

    }

}
