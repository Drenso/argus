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

  private HttpClientInterface $httpClient;

  public function __construct(
      private SerializerInterface $serializer,
      private ProjectPathService $projectPathService,
      private string $gitlabUrl,
      string $gitlabToken)
  {
    $this->httpClient = HttpClient::createForBaseUri($gitlabUrl, [
        'auth_bearer' => $gitlabToken,
    ]);
  }

  /**
   * Retrieve response from the Gitlab API
   *
   * @param Project|null $project
   * @param string       $method
   * @param string       $endpoint
   * @param array        $requestOptions
   *
   * @return array|null
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function projectApi(?Project $project, string $method, string $endpoint, array $requestOptions = []): ?array
  {
    $url = sprintf(
        '%s/%s/projects%s%s%s%s',
        $this->gitlabUrl,
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
      $response = $this->httpClient->request($method, $url, $requestOptions);

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
