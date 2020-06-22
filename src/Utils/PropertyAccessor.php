<?php

namespace App\Utils;

use App\Provider\Gitlab\Exception\MissingPropertyException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class PropertyAccessor
{
  /**
   * @var PropertyAccessorInterface
   */
  private $propertyAccessor;

  /**
   * Check whether the property exists in the given object
   *
   * @param object|array                 $object
   * @param string|PropertyPathInterface $property
   *
   * @return mixed
   */
  public function getProperty($object, string $property)
  {
    if (!$this->propertyAccessor){
      $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
          ->enableExceptionOnInvalidIndex()
          ->getPropertyAccessor();
    }

    try {
      return $this->propertyAccessor->getValue($object, $property);
    } catch (AccessException $e) {
      throw new MissingPropertyException($property, $e);
    }
  }
}
