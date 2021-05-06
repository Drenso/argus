<?php

namespace App\Entity;

use App\Repository\ProjectEnvironmentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Drenso\Shared\Database\Traits\IdTrait;
use InvalidArgumentException;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectEnvironmentRepository::class)
 * @ORM\Table()
 */
class ProjectEnvironment
{
  const STATE_OK = 'ok';
  const STATE_UNKNOWN = 'unknown';
  const STATE_RUNNING = 'running';
  const STATE_FAILED = 'failed';

  const STATES = [
      self::STATE_OK,
      self::STATE_RUNNING,
      self::STATE_FAILED,
      self::STATE_UNKNOWN,
  ];

  use IdTrait;

  /**
   * @var Project
   *
   * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="environments")
   *
   * @Serializer\Exclude()
   */
  private $project;

  /**
   * @var int
   *
   * @ORM\Column(type="integer")
   *
   * @Assert\NotNull()
   */
  private $gitlabId;

  /**
   * Environment name
   *
   * @var string
   *
   * @ORM\Column(type="string", length=255)
   *
   * @Assert\NotBlank()
   */
  private $name = '';

  /**
   * The current state of the environment
   *
   * @var string
   *
   * @ORM\Column(type="string", length=10)
   *
   * @Assert\Choice(choices=ProjectEnvironment::STATES)
   */
  private $currentState = self::STATE_UNKNOWN;

  /**
   * The last event recorded for this environment
   *
   * @var DateTimeImmutable|null
   *
   * @ORM\Column(type="datetime_immutable", nullable=true)
   */
  private $lastEvent;

  public function __construct(Project $project, int $gitlabId)
  {
    $this->project  = $project;
    $this->gitlabId = $gitlabId;
  }

  public function getProject(): Project
  {
    return $this->project;
  }

  public function getGitlabId(): int
  {
    return $this->gitlabId;
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

  public function getCurrentState(): string
  {
    return $this->currentState;
  }

  public function setCurrentState(string $currentState): self
  {
    if (!in_array($currentState, self::STATES)) {
      throw new InvalidArgumentException(sprintf('State %s is not valid', $currentState));
    }

    $this->currentState = $currentState;

    return $this;
  }

  public function setCurrentStateFromGitlab(string $state): self
  {
    switch ($state) {
      case 'created':
      case 'running':
        return $this->setCurrentState(self::STATE_RUNNING);
      case 'success':
        return $this->setCurrentState(self::STATE_OK);
      case 'failed':
      case 'canceled':
      case 'cancelled':
        return $this->setCurrentState(self::STATE_FAILED);
      default:
        return $this->setCurrentState(self::STATE_UNKNOWN);
    }
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

}
