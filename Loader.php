<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <gecko@dvp.io> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Antoine "Gecko" Pous
 * ----------------------------------------------------------------------------
 */
 
class Loader {
  protected static $parentPath = null;
  protected static $paths = array();
  protected static $nsChar = '\\';
  protected static $initialized = false;
  protected static $files = array(__FILE__);
  protected static $fileExt = '.php';
 
  protected static function initialize() {
    
    if (static::$initialized) {
      return;
    }
    
    static::$initialized = true;
    static::$parentPath = __FILE__;
    
    for ($i=substr_count(get_class(), static::$nsChar);$i>=0;$i--) {
        static::$parentPath = dirname(static::$parentPath);
    }
    
  }
 
  public static function register($path, $namespace, $fileExt = false) {
      
    if (!static::$initialized) {
      static::initialize();
    }
  
    static::$paths[] = array(
      'namespace' => $namespace,
      'path'      => trim($path,DIRECTORY_SEPARATOR),
      'fileExt'   => !empty($fileExt) && trim($fileExt) != '' ? $fileExt : static::$fileExt
    );
  
  }
 
  public static function load($class) {
    
    if (class_exists($class,false)) {
      return;
    }
    
    if (!static::$initialized) {
      static::initialize();
    }
        
    foreach (static::$paths as $ns) {
      
      $className = substr($class, strlen($ns['path'].static::$nsChar), strlen($ns['namespace'])); 
      if (!isset($ns['namespace']) || $ns['namespace'] === $className) {
        
        $className = str_replace(static::$nsChar, DIRECTORY_SEPARATOR, ltrim($className,static::$nsChar));
        $classPath = static::$parentPath.DIRECTORY_SEPARATOR.$ns['path'];
                
        if(is_dir($classPath.DIRECTORY_SEPARATOR.$className)) {
          $classPath = $classPath.DIRECTORY_SEPARATOR.$className;
        }
                
        $classFile = $classPath.DIRECTORY_SEPARATOR.$className.$ns['fileExt'];

        if (stream_resolve_include_path($classFile)) {
          require_once $classFile;
          return true;
        }
                
      }
            
    }
    
    return false;
  }

}
 
spl_autoload_register(array('Loader', 'load'));
?>
