<?php

namespace LaminasGen\Generators;

use LaminasGen\Exceptions\EnoughtArgumentsException;

class ControllerGenerator
{
  public static int $requiredArgs = 2;
  private array $args;

  public function __construct(array $args)
  {
    $this->args = $args;
    if (sizeof($args) > $this::$requiredArgs) {
      echo 'Building "' . $args[1] . '" controller in "' . $args[2] . '" module ...' . "\n";
      $this->generate();
    } else {
      throw new EnoughtArgumentsException("composer laminas-gen controller <YourControllerName> <YourModuleName>");
    }
  }

  public function generate()
  {
    $result = file_get_contents(__DIR__ . "/templates/Controller.txt");
    $result = str_replace("{{controllerName}}", $this->getControllerName(), $result);
    $result = str_replace("{{moduleName}}", $this->getModuleName(), $result);
    var_dump($result);
    $this->writeInFile($result);
    echo "\n\e[0;30;42mSuccessfull generated " . $this->getControllerName() . " controller in " . $this->getModuleName() . "'s module !\e[0m\n";
  }

  public function writeInFile(string $content){
    file_put_contents("toto.php", $content);
  }

  public function getControllerName(): string
  {
    return $this->args[1];
  }

  public function getModuleName(): string
  {
    return $this->args[2];
  }
}
