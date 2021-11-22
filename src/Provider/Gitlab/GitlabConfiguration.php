<?php

namespace App\Provider\Gitlab;

class GitlabConfiguration
{
  public function __construct(
      private string $host,
      private string $baseUrl,
      private string $token,
      private ?int   $defaultMrAssigneeId
  )
  {
  }

  public function getHost(): string
  {
    return $this->host;
  }

  public function getBaseUrl(): string
  {
    return $this->baseUrl;
  }

  public function getToken(): string
  {
    return $this->token;
  }

  public function getDefaultMrAssigneeId(): ?int
  {
    return $this->defaultMrAssigneeId;
  }
}
