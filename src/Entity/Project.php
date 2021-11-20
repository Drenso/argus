<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Drenso\Shared\Database\Traits\IdTrait;
use JMS\Serializer\Annotation as Serializer;
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
   * @ORM\Column(type="string", length=255, unique=true)
   *
   * @Assert\NotBlank()
   */
  private string $name = '';

  /**
   * The last detected gitlab host for this project
   *
   * @var string|null
   *
   * @ORM\Column(type="string", nullable=true)
   */
  private ?string $host = null;

  /**
   * The last event recorded for this project
   *
   * @ORM\Column(type="datetime_immutable", nullable=true)
   */
  private ?DateTimeImmutable $lastEvent;

  /**
   * @var ProjectEnvironment[]|ArrayCollection|PersistentCollection
   *
   * @ORM\OneToMany(targetEntity="App\Entity\ProjectEnvironment", mappedBy="project", fetch="EAGER", cascade={"all"})
   * @ORM\OrderBy({"name" = "ASC"})
   *
   * @Assert\Valid()
   */
  private $environments; // Default in constructor

  public function __construct()
  {
    $this->environments = new ArrayCollection();
  }

  public function fromOther(Project $other)
  {
    return $this
        ->setName($other->getName())
        ->setLastEvent($other->getLastEvent());
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getHost(): ?string
  {
    return $this->host;
  }

  public function setHost(?string $host): self
  {
    $this->host = $host;

    return $this;
  }

  public function getLastEvent(): ?DateTimeImmutable
  {
    return $this->lastEvent;
  }

  public function setLastEvent(?DateTimeImmutable $lastEvent): self
  {
    $this->lastEvent = $lastEvent;

    return $this;
  }

  /**
   * @return ProjectEnvironment[]|ArrayCollection|PersistentCollection
   */
  public function getEnvironments()
  {
    return $this->environments;
  }

  /**
   * @Serializer\VirtualProperty()
   */
  public function getCurrentState(): string
  {
    if ($this->environments->isEmpty()) {
      return ProjectEnvironment::STATE_UNKNOWN;
    }

    $states = array_unique($this->environments->map(function (ProjectEnvironment $environment) {
      return $environment->getCurrentState();
    })->toArray());

    foreach (array_reverse(ProjectEnvironment::STATES) as $state) {
      if (in_array($state, $states)) {
        return $state;
      }
    }

    return ProjectEnvironment::STATE_UNKNOWN;
  }
}
