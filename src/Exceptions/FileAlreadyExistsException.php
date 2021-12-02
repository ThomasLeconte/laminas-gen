<?php

namespace LaminasGen\Exceptions;

class FileAlreadyExistsException extends AbstractException
{
  public function __construct($path)
  {
    $this->message = $path." already exists.";
  }
}
