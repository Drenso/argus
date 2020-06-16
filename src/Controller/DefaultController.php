<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class DefaultController extends AbstractController
{
  /**
   * @Route("/", name="index", options={"expose"=true})
   * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
   */
  public function index()
  {
    // Create the response and disable all forms of caching for it
    $response = ($this->render('base.html.twig'))
        ->setPrivate()
        ->setMaxAge(0)
        ->setSharedMaxAge(0);
    $response->headers->addCacheControlDirective('must-revalidate', true);
    $response->headers->addCacheControlDirective('no-store', true);

    return $response;
  }
}
