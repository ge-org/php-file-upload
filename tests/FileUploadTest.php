<?php

	use Faultier\FileUpload\FileUpload;

	class FileUploadTest extends \PHPUnit_Framework_TestCase {
	
		protected $testDirectory;
		protected $up;
		
		public function setUp() {
			$this->testDirectory = __DIR__.'/test-files';
			@mkdir($this->testDirectory);
			
			$this->up = new FileUpload($this->testDirectory);
		}
		
		public function tearDown() {
			@rmdir($this->testDirectory);
		}
		
		public function testInstantiationWithValidUploadDirectory() {
			$this->assertInstanceOf('Faultier\FileUpload\FileUpload', $this->up);
		}
		
		public function testUploadDirectoryNull() {
			$this->setExpectedException('\InvalidArgumentException', 'The given upload directory does not exist');
			$this->up->setUploadDirectory(null);
		}
		
		public function testUploadDirectoryWithEmptyString() {
			$this->setExpectedException('\InvalidArgumentException', 'The given upload directory does not exist');
			$this->up->setUploadDirectory('');
		}
		
		public function testUploadDirectoryWithFile() {
			$this->setExpectedException('\InvalidArgumentException', 'The given upload directory is not a directory');
			$this->up->setUploadDirectory(__DIR__.'/FileUploadTest.php');
		}
		
		public function testUploadDirectoryWithUnwritable() {
			// TODO
		}
		
		public function testUploadDirectory() {
			$this->up->setUploadDirectory($this->testDirectory);
			$this->assertEquals($this->testDirectory, $this->up->getUploadDirectory());
		}
		
	}

?>