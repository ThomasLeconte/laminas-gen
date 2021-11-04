<?php

namespace LaminasGen\Generators;

use LaminasGen\Data\CacheManager;

class Generator {

  protected CacheManager $cacheManager;

  public function __construct(){
    $this->cacheManager = new CacheManager();
  }
}