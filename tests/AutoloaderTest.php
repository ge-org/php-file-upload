<?php

  class AutoloaderTest extends PHPUnit_Framework_TestCase {
  
    public function setUp() {
      require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Faultier'.DIRECTORY_SEPARATOR.'FileUpload'.DIRECTORY_SEPARATOR.'Autoloader.php';
    }
    
    /**
     * @test
     */
    public function register() {
      $this->assertTrue(Faultier\FileUpload\Autoloader::register());
    }
  
    /**
     * @test
     */
    public function autoload() {
      $this->assertTrue(Faultier\FileUpload\Autoloader::autoload('Faultier\FileUpload\FileUpload'));
      $this->assertFalse(Faultier\FileUpload\Autoloader::autoload(''));
    }
  
  }

?>