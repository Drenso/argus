<?php

namespace App\Provider\Irker\EventHandlers\Project;

use App\Events\Project\ProjectPushEvent;
use App\Provider\Irker\EventHandlers\AbstractEventHandler;
use App\Provider\Irker\IrkerUtils;
use App\Utils\PropertyAccessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProjectPushEventHandler extends AbstractEventHandler implements EventSubscriberInterface
{

  /**
   * @var PropertyAccessor
   */
  private $propertyAccessor;

  public function __construct(
      EventDispatcherInterface $dispatcher, LoggerInterface $logger, PropertyAccessor $propertyAccessor)
  {
    parent::__construct($dispatcher, $logger);

    $this->propertyAccessor = $propertyAccessor;
  }

  public static function getSubscribedEvents()
  {
    return [
        ProjectPushEvent::class => ['onEvent', 0],
    ];
  }

  public function onEvent(ProjectPushEvent $event)
  {
    $this->wrapHandler($event, function () use ($event) {
      $commits   = $event->getCommits();
      $branchUrl = sprintf('%s/tree/%s', $event->getUrl(), $event->getBranch());

      $this->message(sprintf('[%s/%s] %s pushed %d commit%s [ %s ]',
          IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
          IrkerUtils::colorize($event->getBranch(), IrkerUtils::COLOR_BROWN),
          $event->getUser(),
          $event->getTotalCommitCount(),
          $event->getTotalCommitCount() > 1 ? 's' : '',
          IrkerUtils::colorize($branchUrl, IrkerUtils::COLOR_BLUE)
      ));

      // Loop the commits if count <= 2
      if (count($commits) <= 2) {
        foreach ($commits as $commit) {
          // Check commit structure

          // Get commit data
          $sha           = substr($this->getProp($commit, '[id]'), 0, 8);
          $author        = $this->getProp($commit, '[author][name]');
          $commitMessage = $this->getProp($commit, '[message]');
          $url           = sprintf('%s/commit/%s', $event->getUrl(), $sha);

          // Strip enters from commit message
          $commitMessage = preg_replace("/[\n\r]+/", " -- ", $commitMessage);
          $commitMessage = preg_replace("/ -- $/", "", $commitMessage);

          // Create the message
          $this->message(sprintf('[%s/%s] %s: %s (by %s) [ %s ]',
              IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
              IrkerUtils::colorize($event->getBranch(), IrkerUtils::COLOR_BROWN),
              IrkerUtils::colorize($sha, IrkerUtils::COLOR_GREY),
              $commitMessage,
              $author,
              IrkerUtils::colorize($url, IrkerUtils::COLOR_BLUE)));
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
              IrkerUtils::colorize($event->getProjectName(), IrkerUtils::COLOR_LIGHT_RED),
              IrkerUtils::colorize($event->getBranch(), IrkerUtils::COLOR_BROWN),
              $count,
              $count > 1 ? 's' : '',
              $author));
        }
      }
    });
  }

  /**
   * @param object|array                 $object
   * @param string|PropertyPathInterface $prop
   *
   * @return mixed
   */
  private function getProp($object, string $prop)
  {
    return $this->propertyAccessor->getProperty($object, $prop);
  }
}
