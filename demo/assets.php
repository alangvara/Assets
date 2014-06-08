<?php

// load composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';

//load configs
$configs = require 'configs.php';

$assets = new Alangvara\Assets\Assets($configs);

if($_GET['type'] == 'javascripts'){
  $assets->dispatchJavascript($_GET['script']);
}else{
  $assets->dispatchStylesheet($_GET['script']);
}