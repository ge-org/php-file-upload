<?php

  use Faultier\FileUpload\UploadError;
  use Faultier\FileUpload\File;
  use Faultier\FileUpload\Constraint\SizeConstraint;

  class UploadErrorTest extends PHPUnit_Framework_TestCase {
  
    protected $err;
    
    public function setUp() {
      $this->err = new UploadError(UploadError::ERR_FILESYSTEM, '');
    }
    
    /**
     * @test
     */
    public function constructor() {
      $this->assertInstanceOf('Faultier\FileUpload\UploadError', $this->err);
    }
    
    /**
     * @test
     */
    public function type() {
      $this->assertEquals(UploadError::ERR_FILESYSTEM, $this->err->getType());
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function invalidType() {
      $this->err->setType('foo');
    }
    
    /**
     * @test
     */
    public function message() {
      $this->err->setMessage('foo');
      $this->assertEquals('foo', $this->err->getMessage());
    }
    
    /**
     * @test
     */
    public function file() {
      $file = new File;
      $this->err->setFile($file);
      $this->assertEquals($file, $this->err->getFile());
    }
    
    /**
     * @test
     */
    public function constraint() {
      $constraint = new SizeConstraint;
      $this->err->setConstraint($constraint);
      $this->assertEquals($constraint, $this->err->getConstraint());
    }
  }

?>