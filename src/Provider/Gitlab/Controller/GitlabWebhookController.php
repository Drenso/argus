<?php

declare(strict_types=1);

namespace App\Provider\Gitlab\Controller;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/_webhook/gitlab")
 */
class GitlabWebhookController extends AbstractController
{
  private const EventHeader = 'X-Gitlab-Event';
  private const AuthHeader = 'X-Gitlab-Token';

  /**
   * Handles the gitlab webhook
   *
   * @Route("", methods={"POST"})
   *
   * @param Request                  $request
   * @param SerializerInterface      $serializer
   * @param EventDispatcherInterface $eventDispatcher
   *
   * @return Response
   */
  public function webhook(
      Request $request, SerializerInterface $serializer, EventDispatcherInterface $eventDispatcher): Response
  {
    if (!$request->headers->has(self::EventHeader)) {
      throw $this->createNotFoundException();
    }

    if ($this->getParameter('gitlab.auth.enabled')) {
      if (!$request->headers->has(self::AuthHeader)
          || $request->headers->get(self::AuthHeader) !== $this->getParameter('gitlab.auth.token')) {
        throw $this->createAccessDeniedException();
      }
    }

    $payload = $serializer->deserialize($request->getContent(), 'array', 'json');

    $eventDispatcher->dispatch(new IncomingGitlabEvent($request->headers->get(self::EventHeader), $payload));

    return new Response();
  }
}
