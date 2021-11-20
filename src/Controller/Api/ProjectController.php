<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Exception\DuplicateProjectException;
use App\Exception\ProjectNotFoundException;
use App\Repository\ProjectRepository;
use App\Serializer\Entity\ProjectSerializer;
use App\Service\ProjectService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @Route("/api/project", options={"expose"=true})
 */
class ProjectController extends AbstractApiController
{
  /**
   * Get all tracked projects
   *
   * @Route("", methods={"GET"})
   * @IsGranted("ROLE_USER")
   */
  public function list(ProjectRepository $projectRepository): JsonResponse
  {
    return $this->createResponse($projectRepository->findAll(), ['Default', ProjectSerializer::GITLAB_URL]);
  }

  /**
   * Add a new tracked project
   *
   * @Route("", methods={"POST"})
   * @IsGranted("ROLE_USER")
   *
   * @throws Throwable
   */
  public function add(Request $request, ProjectService $projectService, TranslatorInterface $translator): JsonResponse
  {
    try {
      $postedData = $this->getFromBody($request, 'array');
      if (!$path = $postedData['path']){
        return $this->createBadRequestResponse($translator->trans('project.exception.missing-path'));
      }

      return $this->createResponse($projectService->add($path));
    } catch (DuplicateProjectException $e) {
      return $this->createBadRequestResponse($translator->trans('project.exception.duplicate'), $e->getProject());
    } catch (ProjectNotFoundException $e) {
      return $this->createBadRequestResponse($translator->trans('project.exception.not-found'), $e->getProject());
    }
  }

  /**
   * @Route("/{project<\d+>}/mr", methods={"POST"})
   * @IsGranted("ROLE_USER")
   */
  public function createMr(Project $project, ProjectService $projectService): Response
  {
    $projectService->createMergeRequest($project);

    return new Response();
  }

  /**
   * @Route("/mr", methods={"POST"})
   * @IsGranted("ROLE_USER")
   */
  public function createMrs(ProjectService $projectService): Response
  {
    $projectService->createMergeRequest(null);

    return new Response();
  }

  /**
   * Delete the given project (including remote configuration, if supported)
   *
   * @Route("/{project<\d+>}", methods={"DELETE"})
   * @IsGranted("ROLE_USER")
   *
   * @throws Throwable
   */
  public function delete(Project $project, ProjectService $projectService): Response
  {
    $projectService->delete($project);

    return new Response();
  }

  /**
   * Retrieve information about outdated projects
   *
   * @Route("/outdated", methods={"GET"})
   * @IsGranted("ROLE_USER")
   */
  public function outdated(ProjectService $projectService): JsonResponse
  {
    return $this->createResponse($projectService->getOutdated());
  }

  /**
   * Sync the remote configuration for the given project
   *
   * @Route("/{project<\d+>}/sync", methods={"POST"})
   * @IsGranted("ROLE_USER")
   */
  public function sync(Project $project, ProjectService $projectService): Response
  {
    $projectService->sync($project);

    return new Response();
  }

  /**
   * Refreshes the environments information for the given project
   *
   * @Route("/{project<\d+>}/environment/refresh", methods={"POST"})
   * @IsGranted("ROLE_USER")
   *
   * @throws Throwable
   */
  public function refreshEnvironments(Project $project, ProjectService $projectService): JsonResponse
  {
    $projectService->refreshEnvironments($project);
    $this->em->refresh($project);

    return $this->createResponse($project);
  }
}
