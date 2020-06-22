<?php

namespace App\Controller\Api;

use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/project", options={"expose"=true})
 */
class ProjectController extends AbstractApiController
{
  /**
   * Get all tracked projects
   *
   * @Route("", methods={"GET"})
   * @param ProjectRepository $projectRepository
   *
   * @return JsonResponse
   */
  public function list(ProjectRepository $projectRepository): JsonResponse
  {
    return $this->createResponse($projectRepository->findAll());
  }
}
