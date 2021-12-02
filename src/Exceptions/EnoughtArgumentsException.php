<?php

namespace LaminasGen\Exceptions;

class EnoughtArgumentsException extends AbstractException
{
  public function __construct($usage)
  {
    $this->message = "Enought arguments. Please use LaminasGen like that : " . $usage;
  }
}
