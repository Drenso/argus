<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StoredEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Drenso\Shared\Database\Traits\IdTrait;
use RuntimeException;

/**
 * @ORM\Entity(repositoryClass=StoredEventRepository::class)
 */
class StoredEvent
{
  const DIR_INCOMING = 'incoming';
  const DIRS = [
      self::DIR_INCOMING,
  ];

  use IdTrait;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=20)
   */
  private $direction;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean")
   */
  private $handled = false;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean")
   */
  private $fullyHandled = false;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=255)
   */
  private $eventName;

  /**
   * @var string|null
   *
   * @ORM\Column(type="text")
   */
  private $payload;

  public function getDirection(): ?string
  {
    return $this->direction;
  }

  public function setDirection(string $direction): self
  {
    if (!in_array($direction, self::DIRS)) {
      throw new RuntimeException(sprintf('Unsupported direction "%s"', $direction));
    }

    $this->direction = $direction;

    return $this;
  }

  public function isHandled(): bool
  {
    return $this->handled;
  }

  public function setHandled(bool $handled): self
  {
    $this->handled = $handled;

    return $this;
  }

  public function isFullyHandled(): bool
  {
    return $this->fullyHandled;
  }

  public function setFullyHandled(bool $fullyHandled): self
  {
    $this->fullyHandled = $fullyHandled;

    return $this;
  }

  public function getEventName(): ?string
  {
    return $this->eventName;
  }

  public function setEventName(string $eventName): self
  {
    $this->eventName = $eventName;

    return $this;
  }

  public function getPayload(): ?string
  {
    return $this->payload;
  }

  public function setPayload(string $payload): self
  {
    $this->payload = $payload;

    return $this;
  }
}
