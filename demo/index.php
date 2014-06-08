<?php

// load composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';

//load configs
$configs = require 'configs.php';

$assets = new Alangvara\Assets\Assets($configs);

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Alangvara\Assets Demo</title>
    <meta charset="UTF-8">
    <?= $assets->stylesheetTags() ?>
  </head>
  <body>
    <h1>Hello World</h1>
    <?= $assets->javascriptTags() ?>
  </body>
</html>