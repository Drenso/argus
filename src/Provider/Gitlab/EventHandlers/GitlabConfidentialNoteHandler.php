<?php

namespace App\Provider\Gitlab\EventHandlers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabConfidentialNoteHandler extends GitlabNoteHandler implements EventSubscriberInterface
{
  protected function getDiscriminator(): string
  {
    return 'Confidential Note Hook';
  }
}
