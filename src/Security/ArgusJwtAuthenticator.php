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
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use function Symfony\Component\String\u;

class ArgusJwtAuthenticator extends AbstractAuthenticator
{
  public const COOKIE_NAME = 'argusAuthentication';
  private const CLAIM_USERNAME = 'username';

  /**
   * @var string
   */
  private $apiControllerPrefix;

  /**
   * @var DateTimeProvider
   */
  private $dateTimeProvider;
  /**
   * @var string
   */
  private $jwtSecret;
  /**
   * @var string
   */
  private $tokenValidity;

  public function __construct(
      DateTimeProvider $dateTimeProvider, string $apiControllerPrefix, string $jwtSecret, string $tokenValidity)
  {
    $this->dateTimeProvider    = $dateTimeProvider;
    $this->jwtSecret           = $jwtSecret;
    $this->apiControllerPrefix = $apiControllerPrefix;
    $this->tokenValidity       = $tokenValidity;
  }

  public function createCookieForUser(UserInterface $user)
  {
    $jwt = (new Builder())
        ->identifiedBy(bin2hex(random_bytes(20)))
        ->withClaim(self::CLAIM_USERNAME, $user->getUsername())
        ->expiresAt($this->dateTimeProvider->utcNow()->modify('+' . $this->tokenValidity))
        ->getToken(new Sha512(), new Key($this->jwtSecret));

    return Cookie::create(
        self::COOKIE_NAME, $jwt,
        0, '/', NULL, true, true, false, Cookie::SAMESITE_STRICT);
  }

  public function supports(Request $request): ?bool
  {
    return $this->isApiController($request) && $request->cookies->has(self::COOKIE_NAME);
  }

  public function authenticate(Request $request): PassportInterface
  {
    $jwt = $request->cookies->get(self::COOKIE_NAME);
    if (!$jwt) {
      throw new CustomUserMessageAuthenticationException('Auth token was not provided');
    }

    $token = (new Parser())
        ->parse($jwt);
    /** @phan-suppress-next-line PhanDeprecatedFunction */
    if (!$token->verify(new Sha512(), new Key($this->jwtSecret))) {
      throw new CustomUserMessageAuthenticationException('Auth token invalid');
    }

    if ($token->isExpired($this->dateTimeProvider->utcNow())) {
      throw new CustomUserMessageAccountStatusException('Token expired');
    }

    /** @phan-suppress-next-line PhanDeprecatedFunction */
    return new SelfValidatingPassport(new UserBadge($token->getClaim(self::CLAIM_USERNAME)));
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

  private function isApiController(Request $request)
  {
    return u($request->get('_controller'))->startsWith($this->apiControllerPrefix);
  }
}
