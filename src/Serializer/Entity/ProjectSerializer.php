<?php

namespace App\Serializer\Entity;

use App\Entity\Project;
use Drenso\Shared\Serializer\AbstractObjectSerializer;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class ProjectSerializer extends AbstractObjectSerializer implements EventSubscriberInterface
{

  public const GITLAB_URL = 'gitlab_url';
  /**
   * @var string
   */
  private $gitlabUrl;

  public function __construct(string $gitlabUrl)
  {
    $this->gitlabUrl = $gitlabUrl;
  }

  public static function getSubscribedEvents()
  {
    return self::defaultSubscriber(Project::class);
  }

  /**
   * @inheritdoc
   */
  protected function doSerialize(SerializationVisitorInterface $visitor, array $groups, $object, ObjectEvent $event): void
  {
    if (in_array(self::GITLAB_URL, $groups)) {
      $this->addStringProperty($visitor, self::GITLAB_URL, $this->gitlabUrl . '/' . $object->getName());
    }
  }
}
