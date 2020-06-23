<?php

namespace App\Provider\Gitlab\Exception;

use Exception;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GitlabRemoteCallFailedException extends Exception
{
  /**
   * @var ResponseInterface|null
   */
  private $response;

  public function __construct(?ResponseInterface $response, ?ExceptionInterface $e)
  {
    $this->response = $response;

    if ($response) {
      parent::__construct(sprintf('Gitlab remote call failed: %s', $response->getContent(false)));
    } else if ($e) {
      parent::__construct(sprintf('Gitlab remote call failed: %s', $e->getMessage()), 0, $e);
    } else {
      parent::__construct('Gitlab remote call failed for an unknown reason');
    }
  }

  /**
   * @return ResponseInterface|null
   */
  public function getResponse(): ?ResponseInterface
  {
    return $this->response;
  }

}
