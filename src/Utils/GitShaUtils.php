<?php

namespace App\Utils;

class GitShaUtils
{
  public static function getShortSha(string $sha): string
  {
    return substr($sha, 0, 8);
  }

  public static function allZeroShortSha(): string
  {
    return static::getShortSha(static::allZeroSha());
  }

  public static function allZeroSha(): string
  {
    return '0000000000000000000000000000000000000000';
  }
}
