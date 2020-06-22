<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Events\Project\ProjectTagEvent;
use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabTagPushHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getDiscriminator(): string
  {
    return 'Tag Push Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): void
  {
    $data        = $event->getPayload();
    $before      = substr($this->getProp($data, '[before]'), 0, 8);
    $checkoutSha = substr($this->getProp($data, '[checkout_sha]'), 0, 8);

    if ($before == "00000000") {
      // Tag created
      $action = 'created';
      $url    = sprintf('%s/commit/%s', $this->getProp($data, '[repository][homepage]'), $checkoutSha);
    } else {
      $action = 'removed';
      $url    = $this->getProp($data, '[repository][homepage]');
    }

    $this->projectEvent(new ProjectTagEvent(
        $this->getProp($data, '[project][path_with_namespace]'),
        $this->getProp($data, '[user_name]'),
        preg_replace('/refs\/[^\/]*\//', '', $this->getProp($data, '[ref]')),
        $url,
        $action,
        $before,
        $checkoutSha
    ));
  }
}
