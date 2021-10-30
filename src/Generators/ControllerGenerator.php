<?php

namespace LaminasGen\Generators;

use LaminasGen\Exceptions\EnoughtArgumentsException;

class ControllerGenerator
{
  public static $requiredArgs = 2;
  private $args;

  public function __construct(array $args)
  {
    $this->args = $args;
    if (sizeof($args) > $this::$requiredArgs) {
      echo "Building " . $args[1] . " controller in ". $args[2] ."...\n";
    } else {
      throw new EnoughtArgumentsException("composer laminas-gen controller <YourControllerName> <YourModuleName>");
    }
  }

  public function generate()
  {
    //todo
  }
}
