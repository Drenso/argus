<?php

namespace App\Provider\Sentry\Controller;

use App\Provider\Sentry\Async\IncomingSentryEventMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class SentryController extends AbstractController
{
  private const ResourceHeader = 'Sentry-Hook-Resource';
  private const SignatureHeader = 'Sentry-Hook-Signature';

  /**
   * Handles the gitlab webhook
   *
   * @Route("/_webhook/sentry", methods={"POST"})
   */
  public function webhook(Request $request, MessageBusInterface $messageBus): Response
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

    $this->dispatchMessage(new IncomingSentryEventMessage($request->headers->get(self::ResourceHeader), $content));

    return new Response();
  }
}
