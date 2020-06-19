<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectPipelineEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabPipelineHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Pipeline Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data = $event->getPayload();
    $id   = $this->getProp($data, '[object_attributes][id]');
    $url  = $this->getProp($data, '[project][web_url]') . '/pipelines/' . $id;

    $this->projectEvent(new ProjectPipelineEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user][name]'),
        $id,
        $url,
        $this->getProp($data, '[object_attributes][status]'),
        $this->getProp($data, '[object_attributes][sha]'),
        count($this->getProp($data, '[builds]'))
    ));
  }
}
