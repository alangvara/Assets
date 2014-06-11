<?php

return array(

  'concat' => TRUE,
  
  'minify' => TRUE,
  
  'cached' => TRUE,
  
  'debug'  => TRUE,
  
  'javascripts_uri' => '/github/Alangvara/Assets/demo/assets/javascripts/',
  
  'stylesheets_uri' => '/github/Alangvara/Assets/demo/assets/stylesheets/',
  
  'stylesheets_path' => dirname(__FILE__) . '/assets/stylesheets/',
  
  'javascripts_path' => dirname(__FILE__) . '/assets/javascripts/',

  'javascripts' => array(
    'script.js',
    'script2.js'
  ),
  
  'stylesheets' => array(
    'style.css',
    'colors.css'
  ),
  
  //oldest cache version allowed on GTM time
  'max_cache_date' => '2014-06-11 00:00:00 GMT'   

);
