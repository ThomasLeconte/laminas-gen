<?php

namespace LaminasGen\Exceptions;

class UnknownArgumentException extends AbstractException
{
  public function __construct(int $wrongArgumentPosition, string $usage)
  {
    $this->message = "Unknown argument on position ".$wrongArgumentPosition.". Please use LaminasGen like that : " . $usage;
  }
}
