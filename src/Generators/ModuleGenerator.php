<?php

namespace LaminasGen\Generators;

use Exception;
use Composer\Script\Event;
use LaminasGen\Data\Constants;
use LaminasGen\Generators\ControllerGenerator;
use LaminasGen\Exceptions\UNknownArgumentException;
use LaminasGen\Exceptions\EnoughtArgumentsException;
use LaminasGen\Exceptions\ModuleAlreadyExistsException;

class ModuleGenerator extends Generator
{
  public static int $requiredArgs = 2;
  public static string $usage = "composer laminas-gen module <YourModuleName> [Optional: without-extra]";
  private array $args;

    /**
     * @throws \LaminasGen\Exceptions\ModuleNotFoundException
     * @throws EnoughtArgumentsException
     * @throws UNknownArgumentException
     * @throws ModuleAlreadyExistsException
     */
    public function __construct(Event $event)
  {
    parent::__construct($event);
    $this->args = $event->getArguments();
    if (sizeof($this->args) >= self::$requiredArgs) {
      if ($this->checkIfModuleExists()) {
        throw new ModuleAlreadyExistsException($this->getModuleName());
      } else {
        if (sizeof($this->args) == 3) {
          if ($this->args[2] === 'without-extra') {
            echo 'Building "' . $this->args[1] . '" module ...' . "\n";
            $this->generate();
          } else {
            throw new UnknownArgumentException(3, self::$usage);
          }
        } else {
          echo 'Building "' . $this->args[1] . '" module ...' . "\n";
          $this->generate();
          new ControllerGenerator($event, true, [null, $this->getModuleName() . "Controller", $this->getModuleName()]);
        }
      }
    } else {
      throw new EnoughtArgumentsException(self::$usage);
    }
  }

  public function generate()
  {
    $this->cacheManager->addToLog(Constants::LOG_COMMENT, "CREATE MODULE " . $this->getModuleName());
    mkdir('./module/' . $this->getModuleName());
    $directories = ["config", "src", "view", "test", "src/Controller", "src/Form", "src/Model"];
    foreach ($directories as $directory) {
      mkdir('./module/' . $this->getModuleName() . "/" . $directory, 0777, true);
    }
    $this->makeConfig();
    $this->cacheManager->addToLog(Constants::LOG_COMMENT, "END");
    echo "\n\e[0;30;42mSuccessfull generated " . $this->getModuleName() . " module with it configuration !\e[0m\n";
  }

  public function makeConfig()
  {
    $this->makeComposerConfig();
    $this->updateProjectModulesConfig();
    $this->makeModuleConfig();
    $this->makeModule();
    $this->cleanProjectCache();
    exec("composer dump-autoload -o");
    $this->cacheManager->addToLog(Constants::LOG_CREATE_FOLDER, "./module/" . $this->getModuleName() . "/");
  }

  public function makeComposerConfig()
  {
    $oldFileContent = file_get_contents("./composer.json");
    $composerJson = json_decode(file_get_contents('./composer.json'), true);
    $composerJson["autoload"]["psr-4"][$this->getModuleName() . "\\"] = "module/" . $this->getModuleName() . "/src" . "/";
    $composerResult = json_encode($composerJson, JSON_PRETTY_PRINT);
    $composerResult = str_replace("\\/", "/", $composerResult);
    file_put_contents("./composer.json", $composerResult);
    $this->cacheManager->addToLog(Constants::LOG_UPDATE_FILE, "./composer.json", $oldFileContent);
  }

  public function updateProjectModulesConfig()
  {
    $modulesConfigFile = file_get_contents("./config/modules.config.php");
    $oldFileContent = $modulesConfigFile;
    $modulesConfigFile = str_replace("return [", "return [
    '" . $this->getModuleName() . "',", $modulesConfigFile);
    file_put_contents("./config/modules.config.php", $modulesConfigFile);
    $this->cacheManager->addToLog(Constants::LOG_UPDATE_FILE, "./config/modules.config.php", $oldFileContent);
  }

  public function makeModuleConfig()
  {
    $configTemplate = file_get_contents(__DIR__ . "/templates/module-config.txt");
    $configTemplate = str_replace("{{moduleName}}", $this->getModuleName(), $configTemplate);
    file_put_contents("./module/" . $this->getModuleName() . "/config/module.config.php", $configTemplate);
  }

  public function makeModule()
  {
    $configTemplate = file_get_contents(__DIR__ . "/templates/module.txt");
    $configTemplate = str_replace("{{moduleName}}", $this->getModuleName(), $configTemplate);
    file_put_contents("./module/" . $this->getModuleName() . "/src/Module.php", $configTemplate);
  }

  public function checkIfModuleExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/");
  }

  public function getModuleName(): string
  {
    return $this->args[1];
  }
}
