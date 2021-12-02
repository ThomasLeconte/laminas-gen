<?php

namespace LaminasGen\Exceptions;

use Exception;

class AbstractException extends Exception
{
  protected $message;
  protected $code;
  protected $file;
  protected $line;

  public function __construct(string $message, int $code = null)
  {
    $this->message = $message;
    if ($code !== null) {
      $this->code = $code;
    }
  }
}
