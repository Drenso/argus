<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectPushEvent;
use App\Utils\GitShaUtils;
use App\Utils\PropertyAccessor;
use BobV\IrkerUtils\Colorize;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProjectPushEventHandler extends AbstractProjectEventHandler implements EventSubscriberInterface
{
  public function __construct(
      EventDispatcherInterface $dispatcher, LoggerInterface $logger,
      private PropertyAccessor $propertyAccessor)
  {
    parent::__construct($dispatcher, $logger);
  }

  public static function getSubscribedEvents()
  {
    return [
        ProjectPushEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectPushEvent $event): void
  {
    $this->wrapHandler($event, function () use ($event) {
      $commits   = $event->getCommits();
      $branchUrl = sprintf('%s/tree/%s', $event->getUrl(), $event->getBranch());

      if ($event->getBefore() === GitShaUtils::allZeroSha()) {
        // New branch
        $this->message(sprintf('[%s/%s] %s pushed a %s branch with %d commit%s [ %s ]',
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            Colorize::colorize($event->getBranch(), Colorize::COLOR_DARK_RED),
            $this->getUserFromEvent($event),
            Colorize::colorize('new', Colorize::COLOR_GREEN),
            $event->getTotalCommitCount(),
            $event->getTotalCommitCount() > 1 ? 's' : '',
            Colorize::colorize($branchUrl, Colorize::COLOR_BLUE)
        ));
      } elseif ($event->getAfter() === GitShaUtils::allZeroSha()) {
        // Removed branch
        $this->message(sprintf('[%s] %s %s branch %s',
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            $this->getUserFromEvent($event),
            Colorize::colorize('deleted', Colorize::COLOR_DARK_RED),
            $event->getBranch(),
        ));
      } else {
        // Normal push
        $this->message(sprintf('[%s/%s] %s pushed %d commit%s [ %s ]',
            Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
            Colorize::colorize($event->getBranch(), Colorize::COLOR_DARK_RED),
            $this->getUserFromEvent($event),
            $event->getTotalCommitCount(),
            $event->getTotalCommitCount() > 1 ? 's' : '',
            Colorize::colorize($branchUrl, Colorize::COLOR_BLUE)
        ));
      }

      // Loop the commits if count <= 2
      if (count($commits) <= 2) {
        foreach ($commits as $commit) {
          // Check commit structure

          // Get commit data
          $shortSha      = GitShaUtils::getShortSha($this->getProp($commit, '[id]'));
          $author        = $this->getProp($commit, '[author][name]');
          $commitMessage = $this->getProp($commit, '[message]');
          $url           = sprintf('%s/commit/%s', $event->getUrl(), $shortSha);

          // Only use first line from commit message
          $commitMessage = trim(preg_split("/[\n\r]+/", $commitMessage)[0]);

          // Create the message
          $this->message(sprintf('[%s/%s] %s: %s (by %s) [ %s ]',
              Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
              Colorize::colorize($event->getBranch(), Colorize::COLOR_DARK_RED),
              Colorize::colorize($shortSha, Colorize::COLOR_GREY),
              $commitMessage,
              $author,
              Colorize::colorize($url, Colorize::COLOR_BLUE)));
        }
      } else if (count($commits) <= $event->getTotalCommitCount()) {
        // Loop commits to determine push name
        $commitMap = [];

        // Count commits per author
        foreach ($commits as $commit) {
          $author = $this->getProp($commit, '[author][name]');
          if (!array_key_exists($author, $commitMap)) $commitMap[$author] = 0;
          $commitMap[$author]++;
        }

        if (count(array_keys($commitMap)) === 1 && array_keys($commitMap)[0] === $event->getUser()) {
          // Do not create redundant push message
          return;
        }

        // Create the messages
        foreach ($commitMap as $author => $count) {
          $this->message(sprintf('[%s/%s] %d commit%s from %s',
              Colorize::colorize($event->getProjectName(), Colorize::COLOR_LIGHT_RED),
              Colorize::colorize($event->getBranch(), Colorize::COLOR_DARK_RED),
              $count,
              $count > 1 ? 's' : '',
              $author));
        }
      }
    });
  }

  private function getProp(object|array $object, string|PropertyPathInterface $prop): mixed
  {
    return $this->propertyAccessor->getProperty($object, $prop);
  }
}
