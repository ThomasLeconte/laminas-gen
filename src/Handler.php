<?php

namespace LaminasGen;

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

    public static function handle(Event $event)
    {
        $args = $event->getArguments();
        var_dump($args);
        switch ($args[0]) {
            case "controller":
                new ControllerGenerator($args);
                break;
            default:
                throw new EnoughtArgumentsException("composer laminas-gen [module/controller/form] <ModuleName / ControllerName / FormName>");
        }
    }

    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postAutoloadDump(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        // do stuff
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }
}
