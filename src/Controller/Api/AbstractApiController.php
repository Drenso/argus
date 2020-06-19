<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

abstract class AbstractApiController extends AbstractController
{
  /**
   * @var SerializationContextFactoryInterface
   */
  protected $contextFactory;
  /**
   * @var SerializerInterface
   */
  protected $serializer;
  /**
   * @var EntityManagerInterface
   */
  protected $em;

  public function __construct(
      SerializerInterface $serializer, SerializationContextFactoryInterface $contextFactory, EntityManagerInterface $em)
  {
    $this->serializer     = $serializer;
    $this->contextFactory = $contextFactory;
    $this->em             = $em;
  }

  /**
   * Create a response with the correct serialization groups
   *
   * @param mixed      $data
   * @param array|null $serializationGroups
   * @param int        $statusCode
   *
   * @return JsonResponse
   */
  protected function createResponse(
      $data, ?array $serializationGroups = NULL, int $statusCode = Response::HTTP_OK): JsonResponse
  {
    $context = $this->contextFactory->createSerializationContext();
    $context->setGroups($serializationGroups ?? ['Default']);

    return JsonResponse::fromJsonString($this->serializer->serialize($data, 'json', $context), $statusCode);
  }

  /**
   * Create a bad request response with the correct serialization groups
   *
   * @param string     $reason
   * @param mixed      $data
   * @param array|null $serializationGroups
   *
   * @return JsonResponse
   */
  protected function createBadRequestResponse(string $reason, $data = NULL, ?array $serializationGroups = NULL): JsonResponse
  {
    return $this->createResponse([
        'reason'  => $reason,
        'payload' => $data,
    ], $serializationGroups, Response::HTTP_BAD_REQUEST);
  }

  /**
   * Create a bad request response with the correct serialization groups
   *
   * @param string     $reason
   * @param mixed      $data
   * @param array|null $serializationGroups
   *
   * @return JsonResponse
   */
  protected function createUnauthorizedResponse(string $reason, $data = NULL, ?array $serializationGroups = NULL): JsonResponse
  {
    return $this->createResponse([
        'reason'  => $reason,
        'payload' => $data,
    ], $serializationGroups, Response::HTTP_UNAUTHORIZED);
  }

  /**
   * Retrieve the data from the request body
   *
   * @param Request $request
   * @param string  $type
   *
   * @return mixed The requested type
   */
  protected function getFromBody(Request $request, string $type)
  {
    try {
      return $this->serializer->deserialize($request->getContent(), $type, 'json');
    } catch (Throwable $e) {
      throw new BadRequestHttpException('Invalid JSON data');
    }
  }
}
