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
  public function __construct(
      protected SerializerInterface                  $serializer,
      protected SerializationContextFactoryInterface $contextFactory,
      protected EntityManagerInterface               $em)
  {
  }

  /**
   * Create a response with the correct serialization groups
   */
  protected function createResponse(
      mixed $data, ?array $serializationGroups = NULL, int $statusCode = Response::HTTP_OK): JsonResponse
  {
    $context = $this->contextFactory->createSerializationContext();
    $context->setGroups($serializationGroups ?? ['Default']);

    return JsonResponse::fromJsonString($this->serializer->serialize($data, 'json', $context), $statusCode);
  }

  /**
   * Create a bad request response with the correct serialization groups
   */
  protected function createBadRequestResponse(string $reason, mixed $data = NULL, ?array $serializationGroups = NULL): JsonResponse
  {
    return $this->createResponse([
        'reason'  => $reason,
        'payload' => $data,
    ], $serializationGroups, Response::HTTP_BAD_REQUEST);
  }

  /**
   * Create a bad request response with the correct serialization groups
   */
  protected function createUnauthorizedResponse(string $reason, mixed $data = NULL, ?array $serializationGroups = NULL): JsonResponse
  {
    return $this->createResponse([
        'reason'  => $reason,
        'payload' => $data,
    ], $serializationGroups, Response::HTTP_UNAUTHORIZED);
  }

  /**
   * Retrieve the data from the request body
   */
  protected function getFromBody(Request $request, string $type): mixed
  {
    try {
      return $this->serializer->deserialize($request->getContent(), $type, 'json');
    } catch (Throwable $e) {
      throw new BadRequestHttpException('Invalid JSON data');
    }
  }
}
