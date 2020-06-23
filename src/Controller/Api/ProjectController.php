<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Exception\DuplicateProjectException;
use App\Exception\ProjectNotFoundException;
use App\Repository\ProjectRepository;
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
   *
   * @param ProjectRepository $projectRepository
   *
   * @return JsonResponse
   */
  public function list(ProjectRepository $projectRepository): JsonResponse
  {
    return $this->createResponse($projectRepository->findAll());
  }

  /**
   * Add a new tracked project
   *
   * @Route("", methods={"POST"})
   * @IsGranted("ROLE_USER")
   *
   * @param Request             $request
   * @param ProjectService      $projectService
   * @param TranslatorInterface $translator
   *
   * @return JsonResponse
   *
   * @throws Throwable
   */
  public function add(Request $request, ProjectService $projectService, TranslatorInterface $translator): JsonResponse
  {
    $newProject = $this->getFromBody($request, Project::class);
    assert($newProject instanceof Project);

    try {
      return $this->createResponse($projectService->add($newProject));
    } catch (DuplicateProjectException $e) {
      return $this->createBadRequestResponse($translator->trans('project.exception.duplicate'), $newProject);
    } catch (ProjectNotFoundException $e) {
      return $this->createBadRequestResponse($translator->trans('project.exception.not-found'), $newProject);
    }
  }

  /**
   * Sync the remote configuration for the given project
   *
   * @Route("/{project<\d+>}/sync", methods={"POST"})
   * @IsGranted("ROLE_USER")
   *
   * @param Project             $project
   * @param ProjectService      $projectService
   * @param TranslatorInterface $translator
   *
   * @return Response
   */
  public function sync(Project $project, ProjectService $projectService, TranslatorInterface $translator): Response
  {
    $projectService->sync($project);

    return new Response();
  }
}
