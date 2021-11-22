<?php

namespace App\Provider\Gitlab;

use App\Entity\Project;
use App\Provider\Gitlab\Exception\GitlabRemoteCallFailedException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabApiConnector
{
  private const API_BASE = 'api/v4';

  /** @var HttpClientInterface[] */
  private array $httpClients = [];

  public function __construct(
      private SerializerInterface          $serializer,
      private GitlabInstanceDetailsService $gitlabInstanceDetailsService)
  {
  }

  /**
   * Retrieve response from the Gitlab API for a specific project
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function projectApi(Project $project, string $method, string $endpoint, array $requestOptions = []): ?array
  {
    return $this->executeCall(
        $project,
        $this->gitlabInstanceDetailsService->getProjectHost($project),
        $method,
        $endpoint,
        $requestOptions
    );
  }

  /**
   * Retrieve response from the Gitlab API for a specific host
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function gitlabApi(string $host, string $method, string $endpoint, array $requestOptions = []): ?array
  {
    return $this->executeCall(NULL, $host, $method, $endpoint, $requestOptions);
  }

  /**
   * @throws GitlabRemoteCallFailedException
   */
  private function executeCall(?Project $project, string $host, string $method, string $endpoint, array $requestOptions): ?array
  {
    $baseUrl = $this->gitlabInstanceDetailsService->getHostBaseUrl($host);
    if (!array_key_exists($host, $this->httpClients)) {
      $this->httpClients[$host] = HttpClient::createForBaseUri(
          $baseUrl,
          [
              'auth_bearer' => $this->gitlabInstanceDetailsService->getHostToken($host),
          ]
      );
    }

    $url = sprintf(
        '%s/%s/projects%s%s%s%s',
        $baseUrl,
        self::API_BASE,
        $project ? '/' : '',
        $project ? urlencode($project->getName()) : '',
        $project && strlen($endpoint) > 0 ? '/' : '',
        $endpoint
    );
    if (!array_key_exists('headers', $requestOptions)) {
      $requestOptions['headers'] = [];
    }
    $requestOptions['headers']['Accept'] = 'application/json';

    try {
      $response = $this->httpClients[$host]->request($method, $url, $requestOptions);

      if ((($response->getHeaders()['content-type'] ?? [])[0] ?? NULL) === 'application/json') {
        return $this->serializer->deserialize($response->getContent(), 'array', 'json');
      }

      return NULL;
    } catch (ExceptionInterface $e) {
      if (isset($response)) {
        throw new GitlabRemoteCallFailedException($response, NULL);
      }

      throw new GitlabRemoteCallFailedException(NULL, $e);
    }
  }
}
