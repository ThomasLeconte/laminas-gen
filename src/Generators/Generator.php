<?php

namespace LaminasGen\Generators;

use Exception;
use Composer\Script\Event;
use LaminasGen\Data\CacheManager;

class Generator {

  protected CacheManager $cacheManager;
  protected Event $event;

  public function __construct($event){
    $this->event = $event;
    $this->cacheManager = new CacheManager();
  }

  public function cleanProjectCache()
  {
    $phpFiles = array_map(function ($file) {
      $length = strlen(".php");
      if (substr_compare($file, ".php", -$length) === 0) {
        return $file;
      }
    }, scandir("./data/cache"));
    if (sizeof($phpFiles) > 0) {
      for ($i = 0; $i < sizeof($phpFiles); $i++) {
        if ($phpFiles[$i] !== NULL) {
          if (!unlink("./data/cache/" . $phpFiles[$i])) {
            throw new Exception("Unable to delete cache file ./data/cache/" . $phpFiles[$i]);
          }
        }
      }
    }
  }

  protected function ask(string $question, bool $breakLine){
    return $breakLine
    ? $this->event->getIO()->ask($question."\n")
    : $this->event->getIO()->ask($question);
  }
}