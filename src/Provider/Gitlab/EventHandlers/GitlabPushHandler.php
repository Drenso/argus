<?php

namespace App\Provider\Gitlab\EventHandlers;

use App\Provider\Gitlab\Events\IncomingGitlabEvent;
use App\Provider\Irker\IrkerUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitlabPushHandler extends AbstractGitlabEventHandler implements EventSubscriberInterface
{

  protected function getEventType(): string
  {
    return 'Push Hook';
  }

  protected function handleEvent(IncomingGitlabEvent $event): ?array
  {
    $data     = $event->getPayload();
    $messages = [];

    // Get repository info
    $repo   = $this->getProp($data, '[repository][name]');
    $branch = preg_replace('/refs\/[^\/]*\//', '', $this->getProp($data, '[ref]'));

    // Order commits on timestamps
    $commits = $this->getProp($data, '[commits]');
    usort($commits, function ($a, $b) {
      return $this->getProp($a, '[timestamp]') <=> $this->getProp($b, '[timestamp]');
    });

    // Loop the commits if count <= 2
    if (count($commits) <= 2) {
      foreach ($commits as $commit) {
        // Check commit structure

        // Get commit data
        $sha           = substr($this->getProp($commit, '[id]'), 0, 8);
        $author        = $this->getProp($commit, '[author][name]');
        $commitMessage = $this->getProp($commit, '[message]');
        $url           = sprintf('%s/commit/%s', $this->getProp($data, '[repository][homepage]'), $sha);

        // Strip enters from commit message
        $commitMessage = preg_replace("/[\n\r]+/", " -- ", $commitMessage);
        $commitMessage = preg_replace("/ -- $/", "", $commitMessage);

        // Create the message
        $messages[] = sprintf('[%s/%s] %s: %s (by %s) [ %s ]',
            $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
            $this->colorize($branch, IrkerUtils::COLOR_BROWN),
            $this->colorize($sha, IrkerUtils::COLOR_GREY),
            $commitMessage,
            $author,
            $this->colorize($url, IrkerUtils::COLOR_BLUE));
      }
    } else {
      // Loop commits to determine push name
      $commitMap = [];
      $url       = sprintf('%s/tree/%s', $this->getProp($data, '[repository][homepage]'), $branch);

      // Count commits per author
      foreach ($commits as $commit) {
        $author = $this->getProp($commit, '[author][name]');
        if (!array_key_exists($author, $commitMap)) $commitMap[$author] = 0;
        $commitMap[$author]++;
      }

      // Create the messages
      foreach ($commitMap as $author => $count) {
        $messages[] = sprintf('[%s/%s] %s pushed %d commit%s [ %s ]',
            $this->colorize($repo, IrkerUtils::COLOR_LIGHT_RED),
            $this->colorize($branch, IrkerUtils::COLOR_BROWN),
            $author,
            $count,
            $count > 1 ? 's' : '',
            $this->colorize($url, IrkerUtils::COLOR_BLUE));
      }
    }

    // Return messages
    return $messages;
  }
}
