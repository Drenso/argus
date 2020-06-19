<?php

namespace App\Controller\Api;

use App\Repository\StoredEventRepository;
use Drenso\Shared\Helper\DateTimeProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/event", options={"expose"=true})
 */
class EventController extends AbstractApiController
{

  /**
   * Retrieve some stats about the events
   *
   * @Route("/stats", methods={"GET"})
   * @IsGranted("ROLE_USER")
   *
   * @param StoredEventRepository $repository
   * @param DateTimeProvider      $dateTimeProvider
   *
   * @return JsonResponse
   */
  public function stats(StoredEventRepository $repository, DateTimeProvider $dateTimeProvider): JsonResponse
  {
    return $this->createResponse([
        'last_hour'  => $repository->getStatsSince($dateTimeProvider->utcNow()->modify('-1 hour')),
        'last_day'   => $repository->getStatsSince($dateTimeProvider->utcNow()->modify('-1 day')),
        'last_month' => $repository->getStatsSince($dateTimeProvider->utcNow()->modify('-1 week')),
        'last_year'  => $repository->getStatsSince($dateTimeProvider->utcNow()->modify('-1 year')),
    ]);
  }

  /**
   * Retrieves recent events which have not been handled
   *
   * @Route("/latest", methods={"GET"})
   * @IsGranted("ROLE_USER")
   *
   * @param StoredEventRepository $repository
   *
   * @return JsonResponse
   */
  public function latest(StoredEventRepository $repository): JsonResponse
  {
    return $this->createResponse([
        'fully_handled'     => $repository->findFullyHandled(),
        'unhandled'         => $repository->findUnhandled(),
        'partially_handled' => $repository->findPartiallyHandled(),
    ]);
  }
}
