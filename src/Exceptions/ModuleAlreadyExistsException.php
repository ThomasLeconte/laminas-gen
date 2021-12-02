<?php

namespace LaminasGen\Exceptions;

class ModuleAlreadyExistsException extends AbstractException
{
  public function __construct($moduleName)
  {
    $this->message = $moduleName . "'s module already exists !";
  }
}
