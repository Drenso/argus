<?php

namespace App\Provider\Gitlab\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabConfidentialIssueEventHandler extends GitlabIssueEventHandler implements EventSubscriberInterface
{
  protected function getDiscriminator(): string
  {
    return 'Confidential Issue Hook';
  }
}
