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
   *
   * @param ProjectRepository $projectRepository
   *
   * @return JsonResponse
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
   * Delete the given project (including remote configuration, if supported)
   *
   * @Route("/{project<\d+>}", methods={"DELETE"})
   * @IsGranted("ROLE_USER")
   *
   * @param Project        $project
   * @param ProjectService $projectService
   *
   * @return Response
   *
   * @throws Throwable
   */
  public function delete(Project $project, ProjectService $projectService): Response
  {
    $projectService->delete($project);

    return new Response();
  }

  /**
   * Sync the remote configuration for the given project
   *
   * @Route("/{project<\d+>}/sync", methods={"POST"})
   * @IsGranted("ROLE_USER")
   *
   * @param Project        $project
   * @param ProjectService $projectService
   *
   * @return Response
   */
  public function sync(Project $project, ProjectService $projectService): Response
  {
    $projectService->sync($project);

    return new Response();
  }
}
