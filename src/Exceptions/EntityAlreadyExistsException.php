<?php

namespace LaminasGen\Exceptions;

class EntityAlreadyExistsException extends AbstractException
{
  public function __construct($entityName)
  {
    $this->message = $entityName . "'s entity already exists.";
  }
}
