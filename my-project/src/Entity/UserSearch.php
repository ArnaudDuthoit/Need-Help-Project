<?php
/**
 * Created by PhpStorm.
 * User: arnau
 * Date: 13/04/2019
 * Time: 10:32
 */

namespace App\Entity;


class UserSearch
{

    /**
     * @var string|null
     */
    private $name;

    /**
     * @return null|string
     */
    public function getname(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setname(string $name): void
    {
        $this->name = $name;
    }


}