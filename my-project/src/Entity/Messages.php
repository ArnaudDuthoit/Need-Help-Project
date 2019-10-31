<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessagesRepository")
 */
class Messages
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     */
    private $fromId;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     */
    private $toId;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ReadAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromId(): ?User
    {
        return $this->fromId;
    }

    public function setFromId(?User $fromId): self
    {
        $this->fromId = $fromId;

        return $this;
    }

    public function getToId(): ?User
    {
        return $this->toId;
    }

    public function setToId(?User $toId): self
    {
        $this->toId = $toId;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->ReadAt;
    }

    public function setReadAt(?\DateTimeInterface $ReadAt): self
    {
        $this->ReadAt = $ReadAt;

        return $this;
    }
}
