<?php

  namespace Faultier\FileUpload;

	class Autoloader {
	
	  static public function register($prepend = false) {
	    return spl_autoload_register(array(new self, 'autoload'), true, $prepend);
	  }
	  
	  static public function autoload($class) {
	  
	    $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.str_replace('\\', '/', $class).'.php';
	  
	    if (is_file($file)) {
	      require $file;
	      return true;
	    } else {
	      return false;
	    }
	  }
	
	}

?>