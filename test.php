<?php

use LaminasGen\Handler;

require_once realpath('vendor/autoload.php');

function dv($var)
{
  die(var_dump($var));
}

$test = new Handler();