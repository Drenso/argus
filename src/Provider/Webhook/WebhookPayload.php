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

  private function __construct(private string $context, private string $status)
  {
    if (!in_array($status, self::STATUSES)) {
      throw new InvalidArgumentException('Invalid status provided.');
    }
  }

  public static function usagePayload(string $status): self
  {
    return new self('usage', $status);
  }

  public static function projectPayload(string $status): self
  {
    return new self('project', $status);
  }

  public function getContext(): string
  {
    return $this->context;
  }

  public function getStatus(): string
  {
    return $this->status;
  }
}
