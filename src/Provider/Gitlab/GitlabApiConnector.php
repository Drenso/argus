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

  /**
   * @var string
   */
  private $gitlabUrl;
  /**
   * @var HttpClientInterface
   */
  private $httpClient;
  /**
   * @var SerializerInterface
   */
  private $serializer;

  public function __construct(SerializerInterface $serializer, string $gitlabUrl, string $gitlabToken)
  {
    $this->serializer = $serializer;
    $this->gitlabUrl  = $gitlabUrl;

    $this->httpClient = HttpClient::createForBaseUri($gitlabUrl, [
        'auth_bearer' => $gitlabToken,
    ]);
  }

  /**
   * Retrieve response from the Gitlab API
   *
   * @param Project $project
   * @param string  $method
   * @param string  $endpoint
   * @param array   $requestOptions
   *
   * @return array
   *
   * @throws GitlabRemoteCallFailedException
   */
  public function projectApi(Project $project, string $method, string $endpoint, array $requestOptions = []): array
  {
    $url = sprintf('%s/%s/projects/%s%s%s',
        $this->gitlabUrl, self::API_BASE, urlencode($project->getName()), strlen($endpoint) > 0 ? '/' : '', $endpoint);
    if (!array_key_exists('headers', $requestOptions)) {
      $requestOptions['headers'] = [];
    }
    $requestOptions['headers']['Accept'] = 'application/json';

    try {
      $response = $this->httpClient->request($method, $url, $requestOptions);

      return $this->serializer->deserialize($response->getContent(), 'array', 'json');
    } catch (ExceptionInterface $e) {
      if (isset($response)) {
        throw new GitlabRemoteCallFailedException($response, NULL);
      }

      throw new GitlabRemoteCallFailedException(NULL, $e);
    }
  }
}
