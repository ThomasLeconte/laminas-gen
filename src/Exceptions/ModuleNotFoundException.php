<?php

namespace LaminasGen\Exceptions;

class ModuleNotFoundException extends AbstractException
{
  public function __construct($moduleName)
  {
    $this->message = $moduleName."'s module not found ! You can create it with laminas-gen-console module <moduleName> !";
  }
}
