<?php

namespace LaminasGen\Generators;

use Exception;
use LaminasGen\Generators\ControllerGenerator;
use LaminasGen\Exceptions\EnoughtArgumentsException;
use LaminasGen\Exceptions\ModuleAlreadyExistsException;

class ModuleGenerator
{
  public static int $requiredArgs = 1;
  private array $args;

  public function __construct(array $args)
  {
    $this->args = $args;
    if ($this->checkIfModuleExists()) {
      throw new ModuleAlreadyExistsException($this->getModuleName());
    } else {
      if (sizeof($args) > $this::$requiredArgs) {
        echo 'Building "' . $args[1] . '" module ...' . "\n";
        $this->generate();
        // new ControllerGenerator([null, $this->getModuleName() . "Controller", $this->getModuleName()]);
      } else {
        throw new EnoughtArgumentsException("composer laminas-gen controller <YourModuleName>");
      }
    }
  }

  public function generate()
  {
    mkdir('./module/'.$this->getModuleName());
    mkdir('./module/' . $this->getModuleName() . "/config", 0777, true);
    mkdir('./module/' . $this->getModuleName() . "/src", 0777, true);
    mkdir('./module/' . $this->getModuleName() . "/view", 0777, true);
    mkdir('./module/' . $this->getModuleName() . "/src/Controller", 0777, true);
    mkdir('./module/' . $this->getModuleName() . "/src/Form", 0777, true);
    mkdir('./module/' . $this->getModuleName() . "/src/Model", 0777, true);
    $this->makeConfig();
    echo "\n\e[0;30;42mSuccessfull generated " . $this->getModuleName() . " module with it configuration !\e[0m\n";
  }

  public function makeConfig()
  {
    $this->makeComposerConfig();
    $this->updateProjectModulesConfig();
    $this->makeModuleConfig();
    $this->cleanProjectCache();
  }

  public function makeComposerConfig(){
    $composerJson = json_decode(file_get_contents('./composer.json'), true);
    $composerJson["autoload"]["psr-4"][$this->getModuleName() . "\\"] = "module/" . $this->getModuleName() . "/src" . "/";
    $composerResult = json_encode($composerJson, JSON_PRETTY_PRINT);
    $composerResult = str_replace("\\/", "/", $composerResult);
    file_put_contents("./composer.json", $composerResult);
  }

  public function updateProjectModulesConfig()
  {
    $modulesConfigFile = file_get_contents("./config/modules.config.php");
    $modulesConfigFile = str_replace("return [", "return [
    '".$this->getModuleName()."',", $modulesConfigFile);
    file_put_contents("./config/modules.config.php", $modulesConfigFile);
  }

  public function makeModuleConfig()
  {
    $configTemplate = file_get_contents(__DIR__ . "/templates/module-config.txt");
    $configTemplate = str_replace("{{moduleName}}", $this->getModuleName(), $configTemplate);
    file_put_contents("./module/" . $this->getModuleName() . "/config/module.config.php", $configTemplate);
  }

  public function checkIfModuleExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/");
  }

  public function getModuleName(): string
  {
    return $this->args[1];
  }

  public function cleanProjectCache()
  {
    $phpFiles = array_map(function ($file) {
      $length = strlen(".php");
      if (substr_compare($file, ".php", -$length) === 0) {
        return $file;
      }
    }, scandir("./data/cache"));
    if(sizeof($phpFiles) > 0){
      for ($i = 0; $i < sizeof($phpFiles); $i++) {
        if($phpFiles[$i] !== NULL){
          if (!unlink("./data/cache/" . $phpFiles[$i])) {
            throw new Exception("Unable to delete cache file ./data/cache/" . $phpFiles[$i]);
          }
        }
      }
    }
  }
}