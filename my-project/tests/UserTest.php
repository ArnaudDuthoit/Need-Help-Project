<?php
/**
 * Created by PhpStorm.
 * User: arnau
 * Date: 01/05/2019
 * Time: 15:14
 */

namespace App\Tests;

use App\Entity\User;
use PHPUnit\Framework\TestCase;


class UserTest extends TestCase
{

    public function testThatWeCanGetTheUsername()
    {
        $user = new User();

        $user->setUsername('Administrateur');

        $this->assertEquals($user->getUsername(), 'Administrateur');

    }

    public function testThatWeCanGetTheRole()
    {
        $user = new User();

        $user->setRole('ROLE_ADMIN');

        $this->assertEquals($user->getRole(), 'ROLE_ADMIN');

    }

    public function testThatWeCanGetUsernameAndRole()
    {


        $user = new User();
        $user->setUsername('Administrateur');
        $user->setRole('ROLE_ADMIN');


        $this->assertEquals($user->getUsernameAndRole(), 'Administrateur ROLE_ADMIN');
    }

    public function testEmailAdressCanBeSent()
    {

        $user = new User();
        $user->setEmail('arnaudduthoit@hotmail.com');

        $this->assertEquals($user->getEmail(), 'arnaudduthoit@hotmail.com');

    }

    public function testEmailVariablesContainCorrectValues()
    {

        $user = new User();

        $user->setUsername('Administrateur');
        $user->setEmail('arnaudduthoit@hotmail.com');


        $emailVariables = $user->getEmailVariables();

        $this->assertArrayHasKey('username', $emailVariables);
        $this->assertArrayHasKey('email', $emailVariables);


        $this->assertEquals($emailVariables['username'], 'Administrateur');
        $this->assertEquals($emailVariables['email'], 'arnaudduthoit@hotmail.com');

    }
}