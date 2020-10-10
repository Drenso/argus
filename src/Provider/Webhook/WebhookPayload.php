<?php

namespace App\Provider\Webhook;

use InvalidArgumentException;

class WebhookPayload
{
  public const STATUS_OK = 'ok';
  public const STATUS_FAILED = 'failed';
  public const STATUS_RUNNING = 'running';

  private const STATUSES = [
      self::STATUS_OK,
      self::STATUS_FAILED,
      self::STATUS_RUNNING,
  ];

  /** @var string */
  private $context;

  /** @var string */
  private $status;

  private function __construct(string $context, string $status)
  {
    if (!in_array($status, self::STATUSES)) {
      throw new InvalidArgumentException('Invalid status provided.');
    }

    $this->context = $context;
    $this->status  = $status;
  }

  public static function usagePayload(string $status): self
  {
    return new self('usage', $status);
  }

  public static function projectPayload(string $status): self
  {
    return new self('project', $status);
  }

  /**
   * @return string
   */
  public function getContext(): string
  {
    return $this->context;
  }

  /**
   * @return string
   */
  public function getStatus(): string
  {
    return $this->status;
  }
}
