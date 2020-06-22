<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Drenso\Shared\Database\Traits\IdTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 * @ORM\Table(indexes={@ORM\Index(columns={"name"})})
 */
class Project
{
  use IdTrait;

  /**
   * Project name, equals the full namespace
   *
   * @var string
   *
   * @ORM\Column(type="string", length=255, unique=true)
   *
   * @Assert\NotBlank()
   */
  private $name = '';

  /**
   * The last event recorded for this project
   *
   * @var DateTimeImmutable|null
   *
   * @ORM\Column(type="datetime_immutable", nullable=true)
   */
  private $lastEvent;

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return Project
   */
  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getLastEvent(): ?DateTimeImmutable
  {
    return $this->lastEvent;
  }

  /**
   * @param DateTimeImmutable $lastEvent
   *
   * @return Project
   */
  public function setLastEvent(DateTimeImmutable $lastEvent): self
  {
    $this->lastEvent = $lastEvent;

    return $this;
  }
}
