<?php

namespace App\Controller\Api;

use App\Security\ArgusJwtAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @Route("/api/auth", options={"expose"=true})
 */
class AuthenticationController extends AbstractApiController
{

  /**
   * This method is responsible for authenticating the users. This is done
   * by setting a cookie containing a signed JWT, which will be validated
   * in our custom authenticator
   *
   * @Route("", methods={"POST"}, name="auth")
   *
   * @param Request                      $request
   * @param UserProviderInterface        $userProvider
   * @param UserPasswordHasherInterface  $passwordEncoder
   * @param ArgusJwtAuthenticator        $authenticator
   *
   * @return Response
   */
  public function authenticate(
      Request $request, UserProviderInterface $userProvider, UserPasswordHasherInterface $passwordEncoder,
      ArgusJwtAuthenticator $authenticator): Response
  {
    $data = $this->getFromBody($request, 'array');
    if (!array_key_exists('username', $data) || !array_key_exists('password', $data)) {
      return $this->createBadRequestResponse('Invalid user data, "username" and "password" property are required', NULL);
    }

    try {
      $user = $userProvider->loadUserByIdentifier($data['username']);
    } catch (UserNotFoundException $e) {
      return $this->createUnauthorizedResponse('Invalid credentials');
    }

    assert($user instanceof PasswordAuthenticatedUserInterface && $user instanceof UserInterface);
    /** @phan-suppress-next-line PhanTypeMismatchArgument */
    if (!$passwordEncoder->isPasswordValid($user, $data['password'])) {
      return $this->createUnauthorizedResponse('Invalid credentials');
    }

    $response = new Response();
    $response->setPrivate();
    $response->headers->setCookie($authenticator->createCookieForUser($user));

    return $response;
  }

  /**
   * Clear the set cookie in the browser
   *
   * @Route("", methods={"DELETE"}, name="auth_clear")
   */
  public function clear()
  {
    $response = new Response();
    $response->setPrivate();
    $response->headers->clearCookie(ArgusJwtAuthenticator::COOKIE_NAME);

    return $response;
  }

  /**
   * Endpoint to verify authentication is still valid
   *
   * @Route("/test", methods={"GET"}, name="auth_test")
   *
   * @return Response
   */
  public function test(): Response
  {
    if ($this->isGranted('ROLE_USER')) {
      return new Response();
    }

    return new Response(NULL, Response::HTTP_FORBIDDEN);
  }
}
