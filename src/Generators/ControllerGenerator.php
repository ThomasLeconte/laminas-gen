<?php

namespace LaminasGen\Generators;

use Exception;
use Composer\Script\Event;
use LaminasGen\Data\Constants;
use LaminasGen\Exceptions\ModuleNotFoundException;
use LaminasGen\Exceptions\EnoughtArgumentsException;
use LaminasGen\Exceptions\FileAlreadyExistsException;

class ControllerGenerator extends Generator
{
  public static int $requiredArgs = 3;
  private array $args;
  private bool $calledByModuleGenerator = false;

  public function __construct(Event $event, bool $calledByModuleGenerator = null)
  {
    parent::__construct($event);
    $this->args = $event->getArguments();
    if ($calledByModuleGenerator) {
      $this->calledByModuleGenerator = true;
    }
    if (sizeof($this->args) >= $this::$requiredArgs) {
      if ($this->checkIfModuleExists()) {
        echo 'Building "' . $this->args[1] . '" controller in "' . $this->args[2] . '" module ...' . "\n";
        $this->generate();
      } else {
        throw new ModuleNotFoundException($this->getModuleName());
      }
    } else {
      throw new EnoughtArgumentsException("composer laminas-gen controller <YourControllerName> <YourModuleName>");
    }
  }

  public function generate()
  {
    if (!$this->calledByModuleGenerator) $this->cacheManager->addToLog(
      Constants::LOG_COMMENT,
      "CREATE CONTROLLER " . $this->getControllerName() . " IN MODULE " . $this->getModuleName()
    );
    $result = file_get_contents(__DIR__ . "/templates/controller.txt");
    $result = str_replace("{{controllerName}}", $this->getControllerName(), $result);
    $result = str_replace("{{moduleName}}", $this->getModuleName(), $result);
    $this->writeInFile($result);
    $this->updateModuleConfig();
    $this->createAllViews();
    $this->cleanProjectCache();
    if (!$this->calledByModuleGenerator) $this->cacheManager->addToLog(
      Constants::LOG_COMMENT,
      "END"
    );
    echo "\n\e[0;30;42mSuccessfull generated " . $this->getControllerName() . " controller in " . $this->getModuleName() . "'s module !\e[0m\n";
  }

  public function writeInFile(string $content)
  {
    if ($this->checkIfModuleControllerFolderExists() == false) {
      mkdir("./module/" . $this->getModuleName() . "/src/Controller/", 0777, true);
      echo "\n\e[1;37;45mController folder not found. Laminas-gen has created it into /module/" . $this->getModuleName() . "/src folder.\e[0m\n";
    }
    if ($this->checkIfControllerExists()) {
      throw new FileAlreadyExistsException("./module/" . $this->getModuleName() . "/src/Controller/" . $this->getControllerName() . ".php");
    } else {
      file_put_contents("./module/" . $this->getModuleName() . "/src/Controller/" . $this->getControllerName() . ".php", $content);
    }
    if (!$this->calledByModuleGenerator) $this->cacheManager->addToLog(
      Constants::LOG_CREATE_FILE,
      "./module/" . $this->getModuleName() . "/src/Controller/" . $this->getControllerName() . ".php"
    );
  }

  public function updateModuleConfig()
  {
    $finalControllerName = strtolower(strpos($this->getControllerName(), "Controller") ?
      str_replace("Controller", "", $this->getControllerName()) : $this->getControllerName());
    if (file_exists("./module/" . $this->getModuleName() . "/config/module.config.php")) {
      $oldFileContent = file_get_contents("./module/" . $this->getModuleName() . "/config/module.config.php");
      $moduleConfig = require("./module/" . $this->getModuleName() . "/config/module.config.php");
      if (!array_key_exists("controllers", $moduleConfig)) {
        $moduleConfig["controllers"] = array();
        $moduleConfig["controllers"]["factories"] = array();
      } else {
        if (!array_key_exists("factories", $moduleConfig["controllers"])) $moduleConfig["controllers"]["factories"] = array();
      }
      $moduleConfig["controllers"]["factories"] += ["Controller\\" . $this->getControllerName() => "Factory\\InvokableFactory"];
      if (!$moduleConfig["router"]["routes"]) $moduleConfig["router"]["routes"] = array();
      $routeToAdd = [
        $finalControllerName => [
          "type" => "Laminas\\Router\\Http\\Segment",
          "options" => [
            "route" => "/" . $finalControllerName . "[:action[/:id]]",
            'constraints' => [
              'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              'id'     => '[0-9]+',
            ],
            'defaults' => [
              'controller' => "Controller\\" . $this->getControllerName(),
              'action'     => 'index',
            ]
          ]
        ]
      ];
      $moduleConfig["router"]["routes"] += $routeToAdd;
      $moduleFile = file_get_contents("./module/" . $this->getModuleName() . "/config/module.config.php");
      $fileTransformed = str_replace(substr($moduleFile, strpos($moduleFile, "return"), strlen($moduleFile)), "", $moduleFile);
      $fileTransformed = $fileTransformed . $this->returnFormatedArray($moduleConfig);
      $fileTransformed = str_replace("'#__DIR__#", "" . "__DIR__.'", $fileTransformed);
      file_put_contents("./module/" . $this->getModuleName() . "/config/module.config.php", $fileTransformed);
      if (!$this->calledByModuleGenerator) $this->cacheManager->addToLog(
        Constants::LOG_UPDATE_FILE,
        "./module/" . $this->getModuleName() . "/config/module.config.php",
        $oldFileContent
      );
    } else {
      $configTemplate = file_get_contents(__DIR__ . "/templates/module-config-with-controller.txt");
      $configTemplate = str_replace("{{finalControllerName}}", $finalControllerName, $configTemplate);
      $configTemplate = str_replace("{{controllerName}}", $this->getControllerName(), $configTemplate);
      $configTemplate = str_replace("{{moduleName}}", $this->getModuleName(), $configTemplate);
      file_put_contents("./module/" . $this->getModuleName() . "/config/module.config.php", $configTemplate);
      echo "\n\e[1;37;45m" . $this->getModuleName() . "/config/module.config.php does not exists. Laminas-gen has created it for next steps of generation.\e[0m\n";
      if (!$this->calledByModuleGenerator) $this->cacheManager->addToLog(
        Constants::LOG_CREATE_FILE,
        "./module/" . $this->getModuleName() . "/config/module.config.php",
        $configTemplate
      );
    }
  }

  public function createAllViews()
  {
    $actions = ["index", "add", "edit", "delete"];
    $moduleName = strtolower($this->getModuleName());
    $finalControllerName = strtolower(strpos($this->getControllerName(), "Controller") ?
      str_replace("Controller", "", $this->getControllerName()) : $this->getControllerName());
    if (file_exists("./module/" . $this->getModuleName() . "/view" . "/")) {
      mkdir("./module/" . $this->getModuleName() . "/view" . "/" . $moduleName . "/" . $finalControllerName . "/", 0777, true);
      echo "\n\e[1;37;45m/view" . "/" . $moduleName . "/" . $finalControllerName . "/" . " folder not found. Laminas-gen has 
      created it with needed subfolders into /module/" . $this->getModuleName() . " folder.\e[0m\n";
    }
    for ($i = 0; $i < count($actions); $i++) {
      $viewTemplate = file_get_contents(__DIR__ . "/templates/view.txt");
      $viewTemplate = str_replace("{{moduleName}}", $moduleName, $viewTemplate);
      $viewTemplate = str_replace("{{finalControllerName}}", $finalControllerName, $viewTemplate);
      $viewTemplate = str_replace("{{actionName}}", $actions[$i], $viewTemplate);
      file_put_contents("./module/" . $this->getModuleName() . "/view" . "/" . $moduleName . "/" . $finalControllerName . "/" . $actions[$i] . ".phtml", $viewTemplate);
    }
    if (!$this->calledByModuleGenerator) $this->cacheManager->addToLog(
      Constants::LOG_CREATE_FOLDER,
      "./module/" . $this->getModuleName() . "/view" . "/" . $moduleName . "/" . $finalControllerName . "/"
    );
  }

  public function returnFormatedArray($array): string
  {
    $array = var_export($array, true);
    $patterns = [
      "/array \(/" => '[',
      "/^([ ]*)\)(,?)$/m" => '$1]$2',
      "/=>[ ]?\n[ ]+\[/" => '=> [',
      "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3'
    ];
    $arrayExploded = explode("\n", preg_replace(array_keys($patterns), array_values($patterns), $array));
    for ($i = 0; $i < count($arrayExploded); $i++) {
      if (strpos($arrayExploded[$i], "\\")) {
        $workingDirectory = str_replace("\\", "\\\\", getcwd() . "\\module\\" . $this->getModuleName() . "\\config");
        if (strpos($arrayExploded[$i], $workingDirectory)) {
          $arrayExploded[$i] = str_replace($workingDirectory, '#__DIR__#', $arrayExploded[$i]);
        }
        if (strpos($arrayExploded[$i], "=>")) {
          $lineExploded = explode("=>", $arrayExploded[$i]);
          for ($j = 0; $j < count($lineExploded); $j++) {
            if (strpos($lineExploded[$j], "\\\\")) {
              $lineExploded[$j] = str_replace("\\\\", "\\", $lineExploded[$j]);
              $lineExploded[$j] = str_replace("'", "", $lineExploded[$j]);
              if (strpos($lineExploded[$j], ",")) {
                $lineExploded[$j] = str_replace(",", "::class,", $lineExploded[$j]);
              } else {
                $lineExploded[$j] = $lineExploded[$j] . "::class";
              }
              $explodedItem = explode("\\", $lineExploded[$j]);
              if (count($explodedItem) > 1) {
                if (strpos($lineExploded[$j], "InvokableFactory::class") || strpos($lineExploded[$j], "Segment::class") || strpos($lineExploded[$j], "Literal::class")) {
                  $explodedItem = $explodedItem[count($explodedItem) - 1];
                } else {
                  $explodedItem = join("\\", array_slice($explodedItem, count($explodedItem) - 2, 3));
                }
              }
              $lineExploded[$j] = $explodedItem;
            }
          }
          $arrayExploded[$i] = join(" => ", $lineExploded);
        }
      }
    }

    return "return " . join(PHP_EOL, $arrayExploded) . ";";
  }

  public function checkIfModuleExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/");
  }

  public function checkIfModuleControllerFolderExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/src/Controller/");
  }

  public function checkIfModuleViewFolderExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/src/view/");
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
