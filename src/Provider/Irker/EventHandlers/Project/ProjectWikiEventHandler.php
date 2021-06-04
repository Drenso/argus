<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectWikiEvent;
use BobV\IrkerUtils\Colorize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectWikiEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{

  public static function getSubscribedEvents()
  {
    return [
        ProjectWikiEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectWikiEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      if ($message = $event->getMessage()) {
        // Limit message length
        if (strlen($message) > 100) {
          $message = substr($message, 0, 100) . '...';
        }

        switch ($event->getAction()) {
          case 'create':
            $fill = '[%s] Wiki page %s ' . Colorize::colorize('created', Colorize::COLOR_GREEN) . ' by %s: %s [ %s ]';
            break;
          case 'update':
            $fill = '[%s] Wiki page %s ' . Colorize::colorize('updated', Colorize::COLOR_PURPLE) . ' by %s: %s [ %s ]';
            break;
          case 'delete':
            $fill = '[%s] Wiki page %s ' . Colorize::colorize('deleted', Colorize::COLOR_DARK_RED) . ' by %s: %s';

            $this->message(sprintf($fill,
                Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
                $message
            ));

            return;
          default:
            $fill = '[%s] Unknown action on wiki page %s by %s [ %s ]';

            $this->message(sprintf($fill,
                Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
                Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
            ));

            return;
        }

        $this->message(sprintf($fill,
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            $event->getIid(),
            $event->getUser(),
            $message,
            Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
        ));

      } else {
        switch ($event->getAction()) {
          case 'create':
            $fill = '[%s] Wiki page %s ' . Colorize::colorize('created', Colorize::COLOR_GREEN) . ' by %s [ %s ]';
            break;
          case 'update':
            $fill = '[%s] Wiki page %s ' . Colorize::colorize('updated', Colorize::COLOR_PURPLE) . ' by %s [ %s ]';
            break;
          case 'delete':
            $fill = '[%s] Wiki page %s ' . Colorize::colorize('deleted', Colorize::COLOR_DARK_RED) . ' by %s';

            $this->message(sprintf($fill,
                Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
            ));

            return;
          default:
            $fill = '[%s] Unknown action on wiki page %s by %s [ %s ]';

            $this->message(sprintf($fill,
                Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
                Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
            ));

            return;
        }

        $this->message(sprintf($fill,
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            $event->getIid(),
            $event->getUser(),
            Colorize::colorize($event->getUrl(), Colorize::COLOR_BLUE)
        ));
      }
    });
  }
}
