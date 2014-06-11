<?php

namespace Alangvara\Assets;

use CSSmin;
use JSMin;

class Assets{
  
  protected $concat = TRUE;
  
  protected $minify = TRUE;
  
  protected $cached = TRUE;
  
  protected $debug  = FALSE;
  
  protected $javascriptsUri = '/assets/javascripts/';
  
  protected $stylesheetsUri = '/assets/stylesheets/';
  
  protected $javascriptsPath;
  
  protected $stylesheetsPath;
  
  protected $javascripts = array();
  
  protected $stylesheets = array();
  
  protected $maxCacheDate = '0000-00-00 00:00:00'; //oldest cache version allowed 
  
  public function __construct($configs){
    
    if(isset($configs['concat'])){
      $this->concat = $configs['concat'];
    }
    
    if(isset($configs['minify'])){
      $this->minify = $configs['minify'];
    }
    
    if(isset($configs['cached'])){
      $this->cached = $configs['cached'];
    }
    
    if(isset($configs['debug'])){
      $this->debug = $configs['debug'];
    }
    
    if(isset($configs['javascripts_uri'])){
      $this->javascriptsUri = $configs['javascripts_uri'];
    }
    
    if(isset($configs['stylesheets_uri'])){
      $this->stylesheetsUri = $configs['stylesheets_uri'];
    }
    
    if(isset($configs['stylesheets_path'])){
      $this->stylesheetsPath = $configs['stylesheets_path'];
    }else{
      $this->stylesheetsPath = dirname(__FILE__) . '/assets/stylesheets';
    }
    
    if(isset($configs['javascripts_path'])){
      $this->javascriptsPath = $configs['javascripts_path'];
    }else{
      $this->javascriptsPath = dirname(__FILE__) . '/assets/javascripts';
    }
    
    if(isset($configs['max_cache_date'])){
      $this->maxCacheDate = $configs['max_cache_date'];
    }
    
    $this->javascripts = $this->parseJavascripts($configs['javascripts']);
    $this->stylesheets = $this->parseStylesheets($configs['stylesheets']);
  }
  
  
  public function javascriptTags(){
    $tags = '';
    
    if($this->concat){
      $script = $this->javascriptsUri . 'all.js';
      $tags .= "<script type=\"text/javascript\" src=\"$script\"></script>\n";
    }else{
      foreach($this->javascripts as $js){
        $script = $this->javascriptsUri . $js;
        $tags .= "<script type=\"text/javascript\" src=\"$script\"></script>\n";
      }
    }
    
    return $tags;
  }
  
  public function stylesheetTags(){
    $tags = '';
    
    if($this->concat){
      $script = $this->stylesheetsUri . 'all.css';
      $tags .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$script\"/>\n";
    }else{
      foreach($this->stylesheets as $css){
        $script = $this->stylesheetsUri . $css;
        $tags .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$script\"/>\n";
      }
    }
    
    return $tags;
  }
  
  public function dispatchJavascript($file){
    
    if($file == 'all.js'){
      $scripts = $this->javascripts;
    }else{
      $scripts = $file;
    }
    
    $now           = time();
    $modifiedSince = $this->httpModifiedSince();
    $lastModified  = $this->lastModified($this->javascriptsPath, $scripts);
    $maxCacheDate  = strtotime($this->maxCacheDate);
    
    if($maxCacheDate > $lastModified){
      
      if($maxCacheDate > $now){
        $lastModified = $now;
      }else{
        $lastModified = $maxCacheDate;
      }
    }
    
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) ." GMT");
    header('Cache-Control: public');
    
    if($this->cached && $modifiedSince >= $lastModified){  
      header("HTTP/1.1 304 Not Modified");
    }else{
      header("Content-type: text/javascript; charset: UTF-8");
      echo $this->getSource('javascripts', $scripts);
    }
    
  }
  
  public function dispatchStylesheet($file){
    
    if($file == 'all.css'){
      $scripts = $this->stylesheets;
    }else{
      $scripts = $file;
    }
    
    $modifiedSince = $this->httpModifiedSince();
    $lastModified  = $this->lastModified($this->stylesheetsPath, $scripts);
    
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) ." GMT");
    header('Cache-Control: public');
    
    if($this->cached && $modifiedSince >= $lastModified){
      header("HTTP/1.1 304 Not Modified");
      exit;
    }else{
      header("Content-type: text/css; charset: UTF-8");
      echo $this->getSource('stylesheets', $scripts);
    }
  }
  
  protected function parseJavascripts($files){
    
    $resultFiles = array();
    
    foreach($files as $fl){
      //valida que sea un comodin de y requiere obtener todos los archivos del directorio.
      if(stripos($fl, '*') == (strlen($fl) - 1)){
        $path = substr($fl, 0, strlen($fl) - 1);

        foreach(scandir($this->javascriptsPath . '/' . $path) as $f){
          if(!is_dir($this->javascriptsPath . '/' . $path . $f)){
            $resultFiles[] = $path . $f;
          }
        }
        
      }else{
        $resultFiles[] = $fl;
      }
    }
    
    return $resultFiles;
  }
  
  protected function parseStylesheets($files){
    
    $resultFiles = array();
    
    foreach($files as $fl){
      //valida que sea un comodin de y requiere obtener todos los archivos del directorio.
      if(stripos($fl, '*') == (strlen($fl) - 1)){
        $path = substr($fl, 0, strlen($fl) - 1);

        foreach(scandir($this->stylesheetsPath . '/' . $path) as $f){
          if(!is_dir($this->stylesheetsPath . '/' . $path . $f)){
            $resultFiles[] = $path . $f;
          }
        }
        
      }else{
        $resultFiles[] = $fl;
      }
    }
    
    return $resultFiles;
  }
  
  protected function lastModified($path, $files){
    $lastModified = 0;
    
    if(is_array($files)){
      foreach($files as $fl){
        $modified = filemtime($path . $fl);
        if($modified > $lastModified){
          $lastModified = $modified;
        }
      }
      
      //also validates configs file modification date when loading all.js
      
    }else{
      $lastModified = filemtime($path . $files);
    }
    
    return $lastModified;
  }
  
  protected function httpModifiedSince(){
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
      return @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }else{
      return 0;
    }
  }
  
  protected function getSource($type, $files){
    $source = '';
    
    if($type == 'javascripts'){
      $path = $this->javascriptsPath;
    }else{
      $path = $this->stylesheetsPath;
    }
    
    if(is_array($files)){
      foreach($files as $fl){
        $source .= file_get_contents($path . $fl);
      }
    }else{
      $source .= file_get_contents($path . $files);
    }
    
    if($this->minify){
      if($type == 'javascripts'){
        $source = JSmin::minify($source);
      }else{
        $cssMin = new CSSmin();
        $source = $cssMin->run($source);
      } 
    }
    
    return $source;
  }
  
  
}
