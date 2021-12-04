<?php

namespace LaminasGen;

use Exception;
use Composer\Script\Event;
use LaminasGen\Data\CacheManager;
use Composer\Installer\PackageEvent;
use LaminasGen\Data\Constants;
use LaminasGen\Generators\EntityGenerator;
use LaminasGen\Generators\ModuleGenerator;
use LaminasGen\Generators\ControllerGenerator;
use LaminasGen\Exceptions\EnoughtArgumentsException;

class Handler
{
    public function __construct()
    {
        echo "Build Handler.php";
    }

    public static function handle(Event $event)
    {
        try{
            $finalArgs = $event->getArguments();
            if (sizeof($finalArgs) > 0) {
                switch ($finalArgs[0]) {
                    case "controller":
                        new ControllerGenerator($event);
                        break;
                    case "module":
                        new ModuleGenerator($event);
                        break;
                    case "entity":
                        new EntityGenerator($event);
                        break;
                    case "undo":
                        $manager = new CacheManager();
                        $manager->undoChanges();
                        break;
                    case "undo-all":
                        $manager = new CacheManager();
                        $manager->undoChanges(false);
                        break;
                    default:
                        throw new EnoughtArgumentsException("composer laminas-gen [module/controller/entity] <ModuleName / ControllerName / EntityName>");
                }
            } else {
                throw new EnoughtArgumentsException(("composer laminas-gen [module/controller/entity] <ModuleName / ControllerName / EntityName>"));
            }
        }catch(Exception $e){
            fwrite(STDERR, "\n\e[1;37;41m" . $e->getMessage() . "\e[0m\n" . PHP_EOL);
        }
    }

    // public static function postAutoloadDump(Event $event)
    // {
    //     $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
    //     require $vendorDir . '/autoload.php';
    // }

    public static function postPackageInstall(Event $event)
    {
        $composerJson = json_decode(file_get_contents('./composer.json'), true);
        if (!array_key_exists("scripts", $composerJson)) {
            $composerJson["scripts"] = array();
        }
        $composerJson["scripts"][Constants::COMMAND_PREFIX] = Constants::COMMAND;
        $composerResult = json_encode($composerJson, JSON_PRETTY_PRINT);
        $composerResult = str_replace("\\/", "/", $composerResult);
        file_put_contents("./composer.json", $composerResult);
    }

    // public static function warmCache(Event $event)
    // {
    //     // make cache toasty
    // }
}
