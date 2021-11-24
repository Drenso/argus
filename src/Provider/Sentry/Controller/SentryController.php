<?php

namespace App\Provider\Sentry\Controller;

use App\Provider\Sentry\Events\IncomingSentryEvent;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SentryController extends AbstractController
{
  private const ResourceHeader = 'Sentry-Hook-Resource';
  private const SignatureHeader = 'Sentry-Hook-Signature';

  /**
   * Handles the gitlab webhook
   *
   * @Route("/_webhook/sentry", methods={"POST"})
   */
  public function webhook(
      Request $request, SerializerInterface $serializer, EventDispatcherInterface $eventDispatcher): Response
  {
    if (!$request->headers->has(self::ResourceHeader) || !$request->headers->has(self::SignatureHeader)) {
      throw $this->createNotFoundException();
    }

    // Verify sentry signature
    $content          = $request->getContent();
    $requestSignature = $request->headers->get(self::SignatureHeader);
    if ($requestSignature !== hash_hmac('SHA256', $content, $this->getParameter('sentry.webhook.secret'))) {
      throw $this->createAccessDeniedException();
    }

    $payload = $serializer->deserialize($content, 'array', 'json');

    $eventDispatcher->dispatch(new IncomingSentryEvent($request->headers->get(self::ResourceHeader), $payload));

    return new Response();
  }
}
