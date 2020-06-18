<?php

namespace App\Controller\Api;

use App\Repository\StoredEventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/event", options={"expose"=true})
 */
class EventController extends AbstractApiController
{
  /**
   * Retrieves all events
   *
   * @Route("", methods={"GET"})
   * @IsGranted("ROLE_USER")
   *
   * @param StoredEventRepository $repository
   *
   * @return JsonResponse
   */
  public function list(StoredEventRepository $repository): JsonResponse
  {
    return $this->createResponse($repository->findAll());
  }
}
