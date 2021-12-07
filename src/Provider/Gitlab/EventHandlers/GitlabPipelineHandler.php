<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectPipelineEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabPipelineHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{
  use IgnoreBranchNameTrait;

  protected function getDiscriminator(): string
  {
    return 'Pipeline Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data = $event->getPayload();

    if ($this->isBranchIgnored($this->getProp($data, '[object_attributes][ref]'))) {
      return;
    }

    $id  = $this->getProp($data, '[object_attributes][id]');
    $url = $this->getProp($data, '[project][web_url]') . '/pipelines/' . $id;

    $this->projectEvent(new ProjectPipelineEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[project][web_url]'),
        $this->getProp($data, '[user][name]'),
        $this->getProp($data, '[user][username]'),
        $id,
        $url,
        $this->getProp($data, '[object_attributes][status]'),
        $this->getProp($data, '[object_attributes][sha]'),
        count($this->getProp($data, '[builds]'))
    ));
  }
}
