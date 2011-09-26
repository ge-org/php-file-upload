<?php

	use Faultier\FileUpload\FileUpload;

	class FileUploadTest extends \PHPUnit_Framework_TestCase {
	
		protected $up;
		
		public function testConstructor() {
			$up = new FileUpload('./');
			$this->assertInstanceOf('\Faultier\FileUpload\FileUpload', $up);
		}
		
	}

?>