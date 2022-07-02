<?php

namespace LaminasGen\Generators;

use Composer\Script\Event;
use LaminasGen\Data\Constants;
use LaminasGen\Exceptions\ModuleNotFoundException;
use LaminasGen\Exceptions\EnoughtArgumentsException;
use LaminasGen\Exceptions\EntityAlreadyExistsException;

class EntityGenerator extends Generator
{

  public static int $requiredArgs = 3;
  private array $args;

  public function __construct(Event $event)
  {
    parent::__construct($event);
    $this->args = $event->getArguments();
    if (sizeof($this->args) >= $this::$requiredArgs) {
      if ($this->checkIfModuleExists()) {
        if(!$this->checkIfEntityExists()){
          echo 'Building "' . $this->args[1] . '" entity in "' . $this->args[2] . '" module ...' . "\n";
          $this->generate();
        }else{
          throw new EntityAlreadyExistsException($this->getEntityName());
        }
      } else {
        throw new ModuleNotFoundException($this->getModuleName());
      }
    } else {
      throw new EnoughtArgumentsException("composer laminas-gen entity <ModuleName> <YourEntityName>");
    }
  }

  public function generate(){
    $this->cacheManager->addToLog(Constants::LOG_COMMENT, "CREATE ENTITY " . $this->getEntityName());

    echo "\n\e[1;37;46m# Entity generation\n# During generation, you will be able to give a type on your properties. You can choose among all primary PHP types.\e[0m\n\n";

    $propertyName = $this->ask("\n\e[1;37;45mType here your first property name or press ENTER for leave :\e[0m", true);
    if($propertyName !== null){
      $propertyType = $this->ask("\n\e[1;37;45mWhat is type of $propertyName property ? Press ENTER to leave question\e[0m", true);
      $finalProp = array("type" => $propertyType, "name" => $propertyName);
      $properties = [];
      while($propertyName != null){
        array_push($properties, $finalProp);
        $propertyName = $this->ask("\n\e[1;37;45mType here your next property name or press ENTER for leave :\e[0m", true);
        if($propertyName == null) break;
        $propertyType = $this->ask("\n\e[1;37;45mWhat is type of $propertyName property ?\e[0m", true);
        $finalProp = array("type" => $propertyType, "name" => $propertyName);
      }
    }
    $this->generateEntityFile($properties);
    $this->generateEntityTableFile();
    echo "\n\e[0;30;42mSuccessfull generated " . $this->getEntityName() . " entity in " . $this->getModuleName() . "'s module !\e[0m\n";
    $this->cacheManager->addToLog(Constants::LOG_COMMENT, "END");
  }

  public function generateEntityFile(array $properties){
    $template = file_get_contents(__DIR__ . "/templates/entity.txt");
    $template = str_replace("{{entityName}}", $this->getEntityName(), $template);
    $template = str_replace("{{moduleName}}", $this->getModuleName(), $template);
    $propertiesResult = "";
    $exchangeArrayProperties = "";
    $gettersSetters = "";
    foreach ($properties as $property) {
      $propertiesResult .= $property["type"] != null
          ? "\tprivate ". $property["type"] ." $" . $property["name"] . ";\n"
          : "\tprivate $" . $property["name"] . ";\n";
      $exchangeArrayProperties .= "\t\t" . '$this->' . $property["name"] . ' = !empty($data["' . $property["name"] . '"]) ? $data["' . $property["name"] . '"] : null;' . "\n";
      $propertyNameTransformed = ucfirst(strtolower($property["name"]));
      $potentialGetterType = $property["type"] != null ? " : ".$property["type"] : "";
      $potentialSetterType = $property["type"] != null ? $property["type"]." " : "";
      $gettersSetters .= "\t"."public function get".$propertyNameTransformed."()$potentialGetterType {\n\t\treturn \$this->".$property["name"].";\n\t}\n\n";
      $gettersSetters .= "\t"."public function set".$propertyNameTransformed."($potentialSetterType\$value) {\n\t\t\$this->".$property["name"]." = \$value;\n\t}\n\n";
    }
    $template = str_replace("{{properties}}", $propertiesResult, $template);
    $template = str_replace("{{exchangeArrayProperties}}", $exchangeArrayProperties, $template);
    $template = str_replace("{{gettersSetters}}", $gettersSetters, $template);
    file_put_contents("./module/" . $this->getModuleName() . "/src/Model" . "/" . $this->getEntityName() . ".php", $template);
    $this->cacheManager->addToLog(
      Constants::LOG_CREATE_FILE,
      "./module/" . $this->getModuleName() . "/src/Model" . "/" . $this->getEntityName() . ".php"
    );
  }

  public function generateEntityTableFile(){
    $template = file_get_contents(__DIR__ . "/templates/entityTable.txt");
    $template = str_replace("{{entityName}}", $this->getEntityName(), $template);
    $template = str_replace("{{moduleName}}", $this->getModuleName(), $template);
    file_put_contents("./module/" . $this->getModuleName() . "/src/Model" . "/" . $this->getEntityName() . "Table.php", $template);
    $this->cacheManager->addToLog(
      Constants::LOG_CREATE_FILE,
      "./module/" . $this->getModuleName() . "/src/Model" . "/" . $this->getEntityName() . "Table.php"
    );
  }

  public function checkIfModuleModelFolderExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/src/Model/");
  }

  public function checkIfEntityExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/src/Model/".$this->getEntityName().".php");
  }

  public function checkIfModuleExists(): bool
  {
    return file_exists("./module/" . $this->getModuleName() . "/");
  }

  public function getEntityName(): string
  {
    return $this->args[1];
  }

  public function getModuleName(): string
  {
    return $this->args[2];
  }
}
