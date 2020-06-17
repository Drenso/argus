<?php

namespace App\Provider\Gitlab\Exception;

use RuntimeException;
use Throwable;

class MissingPropertyException extends RuntimeException
{
  public function __construct(string $property, Throwable $previous)
  {
    parent::__construct(sprintf('The requested property "%s" is not found', $property), 0, $previous);
  }
}
