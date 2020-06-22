<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectWikiEvent;
use App\Provider\Irker\EventHandlers\AbstractEventHandler;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectWikiEventHandler extends AbstractEventHandler implements EventSubscriberInterface
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
            $fill = '[%s] Wiki page %s ' . IrkerUtils::colorize('created', IrkerUtils::COLOR_GREEN) . ' by %s: %s [ %s ]';
            break;
          case 'update':
            $fill = '[%s] Wiki page %s ' . IrkerUtils::colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s: %s [ %s ]';
            break;
          case 'delete':
            $fill = '[%s] Wiki page %s ' . IrkerUtils::colorize('deleted', IrkerUtils::COLOR_LIGHT_RED) . ' by %s: %s';

            $this->message(sprintf($fill,
                IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
                $message
            ));

            return;
          default:
            $fill = '[%s] Unknown action on wiki page %s by %s [ %s ]';

            $this->message(sprintf($fill,
                IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
                IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
            ));

            return;
        }

        $this->message(sprintf($fill,
            IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
            $event->getIid(),
            $event->getUser(),
            $message,
            IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
        ));

      } else {
        switch ($event->getAction()) {
          case 'create':
            $fill = '[%s] Wiki page %s ' . IrkerUtils::colorize('created', IrkerUtils::COLOR_GREEN) . ' by %s [ %s ]';
            break;
          case 'update':
            $fill = '[%s] Wiki page %s ' . IrkerUtils::colorize('updated', IrkerUtils::COLOR_PURPLE) . ' by %s [ %s ]';
            break;
          case 'delete':
            $fill = '[%s] Wiki page %s ' . IrkerUtils::colorize('deleted', IrkerUtils::COLOR_LIGHT_RED) . ' by %s';

            $this->message(sprintf($fill,
                IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
            ));

            return;
          default:
            $fill = '[%s] Unknown action on wiki page %s by %s [ %s ]';

            $this->message(sprintf($fill,
                IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
                $event->getIid(),
                $event->getUser(),
                IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
            ));

            return;
        }

        $this->message(sprintf($fill,
            IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
            $event->getIid(),
            $event->getUser(),
            IrkerUtils::colorize($event->getUrl(), IrkerUtils::COLOR_BLUE)
        ));
      }
    });
  }
}
