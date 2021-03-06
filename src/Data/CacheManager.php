<?php

namespace LaminasGen\Data;

use Exception;

class CacheManager
{

  private $cacheExists;
  private string $logPath = __DIR__ . "/cache/actions.log";

  public function __construct()
  {
    if(!file_exists(__DIR__."/cache/files/")){
      mkdir(__DIR__."/cache/files/", 0777, true);
    }
    $this->cacheExists = file_exists($this->logPath);
  }

  public function addToLog(string $actionType, string $filePath, string $fileContent = null)
  {
    $logContent = $this->cacheExists ? file_get_contents($this->logPath) : null;
    $line = null;
    if ($fileContent != null) {
      $filePathExploded = explode(".", $filePath);
      $fileExtension = $filePathExploded[count($filePathExploded) - 1];
      $cacheFileName = uniqid('cache_') . "." . $fileExtension;
      file_put_contents(__DIR__ . "/cache/files/" . $cacheFileName, $fileContent);
      $line = $actionType . " " . $filePath . " " . __DIR__ . "/cache/files" . "/" . $cacheFileName;
    } else {
      $line = $actionType . " " . $filePath;
    }
    file_put_contents($this->logPath, ($logContent != null ? $logContent . PHP_EOL : "") . $line);
    if(!$this->cacheExists) $this->cacheExists = true;
  }

  public function undoChanges(bool $stepByStep = true)
  {
    if ($this->cacheExists) {
      $logFile = file_get_contents($this->logPath);
      $logFileExploded = array_reverse(explode(PHP_EOL, $logFile));
      $commentsCount = 0;
      for ($i = 0; $i < count($logFileExploded); $i++) {
        $line = explode(" ", $logFileExploded[$i]);
        switch ($line[0]) {
          case Constants::LOG_CREATE_FILE:
            if (!unlink($line[1])) {
              throw new Exception("Unable to delete file in " . $line[1]);
            }
            break;
          case Constants::LOG_UPDATE_FILE:
            $oldFileContent = file_get_contents($line[2]);
            if ($oldFileContent) {
              file_put_contents($line[1], $oldFileContent);
              if (!unlink($line[2])) {
                throw new Exception("Unable to delete file in " . $line[2]);
              }
            }
            break;
          case Constants::LOG_CREATE_FOLDER:
            $this->deleteDirectory($line[1]);
            break;
          case Constants::LOG_COMMENT:
            if($stepByStep){
              $commentsCount++;
              if ($line[1] != "END") {
                $reversed = array_reverse($logFileExploded);
                echo "\n\e[0;30;42mSuccessful cancellation of the last step '" . array_shift($reversed) . "' !\e[0m\n";
              }
            }
            break;
        }
        if($stepByStep && $commentsCount == 2){
          break;
        }
      }
      if (!$stepByStep) echo "\n\e[0;30;42mSuccessful cancellation of all last steps !\e[0m\n";
    } else {
      throw new Exception("You don't have used LaminasGen for undo something...");
    }
    $stepByStep ? $this->deleteLogs($logFile) : $this->deleteLogs();
  }

  public function deleteLogs(string $logFile = null)
  {
    if($logFile != null){
      $logFileExploded = array_reverse(explode(PHP_EOL, $logFile));
      $toRemove = array();
      $commentsCount = 0;
      for($i=0;$i<count($logFileExploded);$i++){
        $line = explode(" ", $logFileExploded[$i]);
        array_push($toRemove, $logFileExploded[$i]);
        if($line[0] == Constants::LOG_COMMENT){
          $commentsCount++;
          if ($commentsCount == 2){
            break;
          }
        }
      }

      $logFileExploded = str_replace(join(PHP_EOL, array_reverse($toRemove)), '', join(PHP_EOL, array_reverse($logFileExploded)));
      if($logFileExploded != ''){
        file_put_contents($this->logPath, $logFileExploded);
      }else{
        if (!unlink($this->logPath)) {
          throw new Exception("Unable to delete library cache file in " . $this->logPath);
        }
      }
    }else{
      if (!unlink($this->logPath)) {
        throw new Exception("Unable to delete library cache file in " . $this->logPath);
      }
      $this->deleteDirectory(__DIR__ . "/cache/files/");
    }
  }

  public function deleteDirectory($dir)
  {
    if (!file_exists($dir)) {
      return true;
    }
    if (!is_dir($dir)) {
      return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
        continue;
      }
      if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
        return false;
      }
    }
    return rmdir($dir);
  }
}
