<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Drenso\Shared\Database\Traits\IdTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @UniqueEntity(fields={"email"})
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
  use IdTrait;

  /**
   * @ORM\Column(type="string", length=180, unique=true)
   *
   * @Assert\NotBlank()
   * @Assert\Email()
   */
  private $email = '';

  /**
   * @var string The hashed password
   * @ORM\Column(type="string")
   *
   * @Assert\NotBlank()
   */
  private $password = '';

  public function getEmail(): string
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
    return $this->getUserIdentifier();
  }

  public function getUserIdentifier(): string
  {
    return $this->email;
  }

  /**
   * @see UserInterface
   */
  public function getRoles(): array
  {
    return ['ROLE_USER'];
  }

  /**
   * @see UserInterface
   */
  public function getPassword(): string
  {
    return (string)$this->password;
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
}
