<?php

namespace App\Security;

use Drenso\Shared\Helper\DateTimeProvider;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ArgusJwtAuthenticator extends AbstractAuthenticator
{
  public const COOKIE_NAME = 'argusAuthentication';
  private const CLAIM_USERNAME = 'username';

  // todo: make configurable
  private const TOKEN_VALIDITY = '+1 week';

  /**
   * @var DateTimeProvider
   */
  private $dateTimeProvider;
  /**
   * @var string
   */
  private $jwtSecret;
  /**
   * @var UserProviderInterface
   */
  private $userProvider;

  public function __construct(
      DateTimeProvider $dateTimeProvider, UserProviderInterface $userProvider, string $jwtSecret)
  {
    $this->dateTimeProvider = $dateTimeProvider;
    $this->jwtSecret        = $jwtSecret;
    $this->userProvider     = $userProvider;
  }

  public function createCookieForUser(UserInterface $user)
  {
    $jwt = (new Builder())
        ->identifiedBy(bin2hex(random_bytes(20)))
        ->withClaim(self::CLAIM_USERNAME, $user->getUsername())
        ->expiresAt($this->dateTimeProvider->utcNow()->modify(self::TOKEN_VALIDITY)->getTimestamp())
        ->getToken(new Sha512(), new Key($this->jwtSecret));

    return Cookie::create(
        self::COOKIE_NAME, $jwt,
        0, '/', NULL, true, true, false, Cookie::SAMESITE_STRICT);
  }

  public function supports(Request $request): ?bool
  {
    return $request->cookies->has(self::COOKIE_NAME);
  }

  public function authenticate(Request $request): PassportInterface
  {
    $jwt = $request->cookies->get(self::COOKIE_NAME);
    if (!$jwt) {
      throw new CustomUserMessageAuthenticationException('Auth token was not provided');
    }

    $token = (new Parser())
        ->parse($jwt);
    if (!$token->verify(new Sha512(), new Key($this->jwtSecret))) {
      throw new CustomUserMessageAuthenticationException('Auth token invalid');
    }

    if ($token->isExpired($this->dateTimeProvider->utcNow())) {
      throw new CustomUserMessageAccountStatusException('Token expired');
    }

    if (!$user = $this->userProvider->loadUserByUsername($token->getClaim(self::CLAIM_USERNAME))) {
      throw new CustomUserMessageAccountStatusException('Account expired');
    }

    return new SelfValidatingPassport($user);
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
  {
    return NULL;
  }

  public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
  {
    $response = new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    $response->headers->clearCookie(self::COOKIE_NAME);

    return $response;
  }
}
