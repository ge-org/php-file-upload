<?php

	use Faultier\FileUpload\File;

	class FileTest extends \PHPUnit_Framework_TestCase {
	
		protected $file;
		
		public function setUp() {
			$this->file = new File();
		}
		
		/**
		 * @test
		 */
		public function instanceIsCreated() {
			$this->assertInstanceOf('Faultier\FileUpload\File', $this->file);
		}
		
		/**
		 * @test
		 */
		public function name() {
			$this->file->setName('Georg');
			$this->assertEquals('Georg', $this->file->getName());
		}
		
		/**
		 * @test
		 */
		public function originalName() {
			$this->file->setOriginalName('Georg');
			$this->assertEquals('Georg', $this->file->getOriginalName());
		}
		
		/**
		 * @test
		 */
		public function temporaryName() {
			$this->file->setTemporaryName('Georg');
			$this->assertEquals('Georg', $this->file->getTemporaryName());
		}
		
		/**
		 * @test
		 */
		public function fieldName() {
			$this->file->setFieldName('Georg');
			$this->assertEquals('Georg', $this->file->getFieldName());
		}
		
		/**
		 * @test
		 */
		public function mimeType() {
			$this->file->setMimeType('Georg');
			$this->assertEquals('Georg', $this->file->getMimeType());
		}
		
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 */
		public function sizeException() {
			$this->file->setSize('Georg');
		}
		
		/**
		 * @test
		 */
		public function size() {
			$this->file->setSize(1234);
			$this->assertEquals(1234, $this->file->getSize());
		}
		
		/**
		 * @test
		 */
		public function errorCode() {
			$this->file->setErrorCode(0);
			$this->assertEquals(UPLOAD_ERR_OK, $this->file->getErrorCode());

			$this->file->setErrorCode(1);
			$this->assertEquals(UPLOAD_ERR_INI_SIZE, $this->file->getErrorCode());
			
			$this->file->setErrorCode(2);
			$this->assertEquals(UPLOAD_ERR_FORM_SIZE, $this->file->getErrorCode());
			
			$this->file->setErrorCode(3);
			$this->assertEquals(UPLOAD_ERR_PARTIAL, $this->file->getErrorCode());
			
			$this->file->setErrorCode(4);
			$this->assertEquals(UPLOAD_ERR_NO_FILE, $this->file->getErrorCode());
			
			$this->file->setErrorCode(6);
			$this->assertEquals(UPLOAD_ERR_NO_TMP_DIR, $this->file->getErrorCode());
			
			$this->file->setErrorCode(7);
			$this->assertEquals(UPLOAD_ERR_CANT_WRITE, $this->file->getErrorCode());
			
			$this->file->setErrorCode(8);
			$this->assertEquals(UPLOAD_ERR_EXTENSION, $this->file->getErrorCode());
		}
	
		/**
		 * @test
		 * @expectedException \InvalidArgumentException
		 */
		public function errorCodeException() {
			$this->file->setErrorCode(5);
		}
		
		/**
		 * @test
		 */
		public function errorCodeMessage() {
			$this->file->setErrorCode(0);
			$this->assertEquals('The file was successfully uploaded', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(1);
			$this->assertEquals('The size exceeds upload_max_filesize set in php.ini', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(2);
			$this->assertEquals('The size exceeds MAX_FILE_SIZE set in the HTML form', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(3);
			$this->assertEquals('The file was only partially uploaded', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(4);
			$this->assertEquals('No file was uploaded', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(6);
			$this->assertEquals('No temporary directory was set', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(7);
			$this->assertEquals('Could not write to disk', $this->file->getErrorMessage());
			
			$this->file->setErrorCode(8);
			$this->assertEquals('File upload stopped due to extension', $this->file->getErrorMessage());
		}
		
		/**
		 * @test
		 */
		public function uploaded() {
			$this->file->setUploaded(true);
			$this->assertTrue($this->file->isUploaded());
			
			$this->file->setUploaded(false);
			$this->assertFalse($this->file->isUploaded());
			
			$this->file->setUploaded(4);
			$this->assertTrue($this->file->isUploaded());
		}
		
		/**
		 * @test
		 */
		public function filePath() {
			$this->file->setFilePath('Georg');
			$this->assertEquals('Georg', $this->file->getFilePath());
		}
		
		/**
		 * @test
		 */
		public function extension() {
			$this->file->setName('Georg.jpg');
			$this->assertEquals('jpg', $this->file->getExtension());
		}
	}

?>