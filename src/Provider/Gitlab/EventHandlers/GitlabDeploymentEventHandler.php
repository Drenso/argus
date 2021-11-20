<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectDeploymentEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabDeploymentEventHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  protected function getDiscriminator(): string
  {
    return 'Deployment Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void{
    $data = $event->getPayload();

    $this->projectEvent(new ProjectDeploymentEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[project][web_url]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[deployable_id]'),
        $this->getProp($data, '[deployable_url]'),
        $this->getProp($data, '[status]'),
        $this->getProp($data, '[environment]'),
        $this->getProp($data, '[short_sha]'),
    ));
  }
}
