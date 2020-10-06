<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectNoteEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectNoteEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{

  public static function getSubscribedEvents()
  {
    return [
        ProjectNoteEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectNoteEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      // Limit note length
      $note = $event->getNote();
      if (strlen($note) > 100) {
        $note = substr($note, 0, 100) . '...';
      }

      switch ($event->getAction()) {
        case 'commit':
          $fill = '[%s] Note to%s commit %s ("%s") added by %s: %s [ %s ]';
          break;
        case 'merge_request':
          $fill = '[%s] Note to%s merge request "%s" (#%s) added by %s: %s [ %s ]';
          break;
        case 'issue':
          $fill = '[%s] Note to%s issue "%s" (#%s) added by %s: %s [ %s ]';
          break;
        case 'snippet':
          // Ignore snippets
          return;
        default:
          $this->message(sprintf('[%s] Unknown update by %s: %s [%s]',
              IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
              $event->getUser(),
              $note,
              IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
          ));

          return;
      }

      $this->message(sprintf($fill,
          IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
          $event->isConfidential() ? (' ' . IrkerUtils::colorize('confidential', IrkerUtils::COLOR_ORANGE)) : '',
          $event->getTitle(),
          $event->getIid(),
          $event->getUser(),
          $note,
          IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
      ));
    });
  }
}
