<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
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
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deadline;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Profile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $profile;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_validate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_key;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $canocnical;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_mail_checked;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_accept_gts;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_accept_newsletter;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_paid;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getIsValidate(): ?bool
    {
        return $this->is_validate;
    }

    public function setIsValidate(bool $is_validate): self
    {
        $this->is_validate = $is_validate;

        return $this;
    }

    public function getResetKey(): ?string
    {
        return $this->reset_key;
    }

    public function setResetKey(?string $reset_key): self
    {
        $this->reset_key = $reset_key;

        return $this;
    }

    public function getCanocnical(): ?string
    {
        return $this->canocnical;
    }

    public function setCanocnical(?string $canocnical): self
    {
        $this->canocnical = $canocnical;

        return $this;
    }

    public function getIsMailChecked(): ?bool
    {
        return $this->is_mail_checked;
    }

    public function setIsMailChecked(?bool $is_mail_checked): self
    {
        $this->is_mail_checked = $is_mail_checked;

        return $this;
    }

    public function getIsAcceptGts(): ?bool
    {
        return $this->is_accept_gts;
    }

    public function setIsAcceptGts(bool $is_accept_gts): self
    {
        $this->is_accept_gts = $is_accept_gts;

        return $this;
    }

    public function getIsAcceptNewsletter(): ?bool
    {
        return $this->is_accept_newsletter;
    }

    public function setIsAcceptNewsletter(?bool $is_accept_newsletter): self
    {
        $this->is_accept_newsletter = $is_accept_newsletter;

        return $this;
    }

    public function getIsPaid(): ?bool
    {
        return $this->is_paid;
    }

    public function setIsPaid(bool $is_paid): self
    {
        $this->is_paid = $is_paid;

        return $this;
    }
}
