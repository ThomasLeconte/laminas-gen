<?php

namespace LaminasGen\Generators;

use LaminasGen\Exceptions\ModuleNotFoundException;
use LaminasGen\Exceptions\EnoughtArgumentsException;
use LaminasGen\Exceptions\FileAlreadyExistsException;

class ControllerGenerator
{
  public static int $requiredArgs = 2;
  private array $args;

  public function __construct(array $args)
  {
    $this->args = $args;
    if ($this->checkIfModuleExists()) {
      if (sizeof($args) > $this::$requiredArgs) {
        echo 'Building "' . $args[1] . '" controller in "' . $args[2] . '" module ...' . "\n";
        $this->generate();
      } else {
        throw new EnoughtArgumentsException("composer laminas-gen controller <YourControllerName> <YourModuleName>");
      }
    } else {
      throw new ModuleNotFoundException($this->getModuleName());
    }
  }

  public function generate()
  {
    $result = file_get_contents(__DIR__ . "/templates/controller.txt");
    $result = str_replace("{{controllerName}}", $this->getControllerName(), $result);
    $result = str_replace("{{moduleName}}", $this->getModuleName(), $result);
    $this->writeInFile($result);
    $this->updateModuleConfig();
    echo "\n\e[0;30;42mSuccessfull generated " . $this->getControllerName() . " controller in " . $this->getModuleName() . "'s module !\e[0m\n";
  }

  public function writeInFile(string $content)
  {
    if ($this->checkIfModuleControllerFolderExists() == false) {
      mkdir("./module/" . $this->getModuleName() . "/src/Controller/", 0777, true);
      echo "\n\e[1;37;45mController folder not found. Laminas-gen has created it into module/" . $this->getModuleName() . "/src folder.\e[0m\n";
    }
    if ($this->checkIfControllerExists()) {
      throw new FileAlreadyExistsException("./module/" . $this->getModuleName() . "/src/Controller/" . $this->getControllerName() . ".php");
    } else {
      file_put_contents("./module/" . $this->getModuleName() . "/src/Controller/" . $this->getControllerName() . ".php", $content);
    }
  }

  public function updateModuleConfig()
  {
    $moduleConfig = file_get_contents("./module/" . $this->getModuleName() . "/config/module.config.php");
    $finalControllerName = strtolower(strpos($this->getControllerName(), "Controller") ? 
      str_replace("Controller", "", $this->getControllerName()) : $this->getControllerName());
    $configTemplate = file_get_contents(__DIR__ . "/templates/controller-routing-config.txt");
    $configTemplate = str_replace("{{finalControllerName}}", $finalControllerName, $configTemplate);
    $configTemplate = str_replace("{{controllerName}}", $this->getControllerName(), $configTemplate);
    $moduleConfig = str_replace("      'routes' => [", "      'routes' => [\n".$configTemplate, $moduleConfig);
    file_put_contents("./module/" . $this->getModuleName() . "/config/module.config.php", $moduleConfig);
  }

  public function checkIfModuleExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/");
  }

  public function checkIfModuleControllerFolderExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/src/Controller/");
  }

  public function checkIfControllerExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/src/Controller/" . $this->getControllerName() . ".php");
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
