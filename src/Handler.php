<?php

namespace LaminasGen;

use Exception;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use LaminasGen\Generators\ControllerGenerator;
use LaminasGen\Exceptions\EnoughtArgumentsException;

class Handler
{
    public function __construct()
    {
        echo "Build Handler.php";
    }

    public static function handle(Event $event = null, $args = null)
    {
        $finalArgs = null;
        if($event == null){
            if($args == null){
                throw new Exception('Please give arguments to command ! Use Laminas-gen like that : composer laminas-gen [module/controller/form] <ModuleName / ControllerName / FormName>');
            }else{
                $finalArgs = $args;
            }
        }else{
            $finalArgs = $event->getArguments();
        }
        switch ($finalArgs[0]) {
            case "controller":
                new ControllerGenerator($args);
                break;
            default:
                throw new EnoughtArgumentsException("composer laminas-gen [module/controller/form] <ModuleName / ControllerName / FormName>");
        }
    }

    // public static function postUpdate(Event $event)
    // {
    //     $composer = $event->getComposer();
    //     // do stuff
    // }

    // public static function postAutoloadDump(Event $event)
    // {
    //     $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
    //     require $vendorDir . '/autoload.php';
    // }

    // public static function postPackageInstall(PackageEvent $event)
    // {
    //     $installedPackage = $event->getOperation()->getPackage();
    //     // do stuff
    // }

    // public static function warmCache(Event $event)
    // {
    //     // make cache toasty
    // }
}
