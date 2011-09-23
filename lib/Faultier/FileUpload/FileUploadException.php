<?php

	namespace Faultier\FileUpload;
	
	use Faultier\FileUpload\File;
	
	class FileUploadException extends \Exception {
	
		const ERR_PHP_UPLOAD = 0;
		const ERR_FILESYSTEM = 1;
		const ERR_CONSTRAINT = 2;
		const ERR_MOVE_FILE = 3;
		
		private $uploadedFile;
		
		public function setUploadedFile(File $file) {
			$this->uploadedFile = $file;
		}
		
		public function getUploadedFile() {
			return $this->uploadedFile;
		}
		
	}

?>