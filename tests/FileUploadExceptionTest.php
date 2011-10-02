<?php
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\FileUploadException;

	class FileUploadExceptionTest extends PHPUnit_Framework_TestCase {
	
		public function testException() {
			$f = new File();
		
			$e = new FileUploadException('foo');
			$e->setUploadedFile($f);
			
			$this->assertEquals($f, $e->getUploadedFile());
		}
	
	}

?>