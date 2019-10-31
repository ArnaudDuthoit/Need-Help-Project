<?php
/**
 * Created by PhpStorm.
 * User: arnau
 * Date: 12/04/2019
 * Time: 10:06
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;

class ProjetSearch
{
    /**
     * @var string|null
     */
    private $projectname;

    /**
     * @var ArrayCollection
     */
    private $tags;

    /**
     * @var ArrayCollection
     */
    private $sort;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return null|string
     */
    public function getProjectname(): ?string
    {
        return $this->projectname;
    }

    /**
     * @param null|string $projectname
     */
    public function setProjectname(string $projectname): void
    {
        $this->projectname = $projectname;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags(): ArrayCollection
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     */
    public function setTags(ArrayCollection $tags): void
    {
        $this->tags = $tags;
    }




}