<?php
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\FileUploadException;

	class FileUploadExceptionTest extends PHPUnit_Framework_TestCase {
	
		/**
		 * @test
		 */
		public function exception() {
			$f = new File();
		
			$e = new FileUploadException('foo');
			$e->setUploadedFile($f);
			
			$this->assertEquals($f, $e->getUploadedFile());
		}
	
	}

?>