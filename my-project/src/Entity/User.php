<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Cocur\Slugify\Slugify;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"username"},
 *     message="Cet Username est déjà utilisé"
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="L'email que vous avez indiqué est déjà utilisé"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\EqualTo(propertyPath="email",message="Votre adresse mail doit etre identique")
     *
     */
    private $confirm_email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password",message="Votre mot de passe doit etre identique")
     *
     */
    public $confirm_password;

    /**
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    private $new_password;

    private $old_password;

    /**
     * @Assert\EqualTo(propertyPath="new_password",message="Votre mot de passe doit etre identique")
     */
    public $confirm_new_password;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Projet", mappedBy="user")
     */
    private $projets;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostLike", mappedBy="user")
     */
    private $likes;

    /**
     * @ORM\Column(type="integer")
     */
    private $Active;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $RegistrationToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetToken;

    public function __construct()
    {
        $this->projets = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSlug() :string
    {

        return (new Slugify())->slugify($this->username); // hello-world
    }

    /**
     * @return Collection|Projet[]
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets[] = $projet;
            $projet->setUser($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->contains($projet)) {
            $this->projets->removeElement($projet);
            // set the owning side to null (unless already changed)
            if ($projet->getUser() === $this) {
                $projet->setUser(null);
            }
        }

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` projet,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles()
    {
        if($this->role == 'ROLE_USER'){
            return ['ROLE_USER'];
        } elseif ($this->role == 'ROLE_ADMIN'){
            return ['ROLE_ADMIN'];
        }
        else {
            RETURN [];
        }
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return Collection|PostLike[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(PostLike $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->Active;
    }

    public function setActive(int $Active): self
    {
        $this->Active = $Active;

        return $this;
    }

    public function getRegistrationToken(): ?string
    {
        return $this->RegistrationToken;
    }

    public function setRegistrationToken(string $RegistrationToken): self
    {
        $this->RegistrationToken = $RegistrationToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewPassword()
    {
        return $this->new_password;
    }

    /**
     * @param mixed $new_password
     */
    public function setNewPassword($new_password): void
    {
        $this->new_password = $new_password;
    }

    /**
     * @return mixed
     */
    public function getConfirmNewPassword()
    {
        return $this->confirm_new_password;
    }

    /**
     * @param mixed $confirm_new_password
     */
    public function setConfirmNewPassword($confirm_new_password): void
    {
        $this->confirm_new_password = $confirm_new_password;
    }

    /**
     * @return mixed
     */
    public function getOldPassword()
    {
        return $this->old_password;
    }

    /**
     * @param mixed $old_password
     */
    public function setOldPassword($old_password): void
    {
        $this->old_password = $old_password;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getUsernameAndRole(){

        return "$this->username $this->role";
    }

    public function getEmailVariables(){

        return [
            'username'=> $this->getUsername(),
            'email' => $this->getEmail()
        ];
    }

    /**
     * @return mixed
     */
    public function getConfirmEmail()
    {
        return $this->confirm_email;
    }

    /**
     * @param mixed $confirm_email
     */
    public function setConfirmEmail($confirm_email): void
    {
        $this->confirm_email = $confirm_email;
    }
}
