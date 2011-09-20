<?php

	namespace Faultier\FileUploader;
	
	class File {
	
		protected $name;
		protected $originalName;
		protected $temporaryName;
		protected $fieldName;
		protected $mimeType;
		protected $size; // in bytes
		protected $errorCode;
		protected $isUploaded;
		
		public function setName($name) {
			$this->name = $name;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function setOriginaleName($name) {
			$this->originalName = $name;
		}
		
		public function getOriginalName() {
			return $this->originalName;
		}
		
		public function setTemporaryName($temporaryName) {
			$this->temporaryName = $temporaryName;
		}
		
		public function getTemporaryName() {
			return $this->temporaryName;
		}
		
		public function setFieldName($field) {
			$this->fieldName = $field;
		}
		
		public function getFieldName() {
			return $this->fieldName;
		}
		
		public function setMimeType($type) {
			$this->mimeType = $type;
		}
		
		public function getMimeType() {
			return $this->mimeType;
		}
		
		public function setSize($size) {
			$this->size = $size;
		}
		
		public function getSize() {
			return $this->size;
		}
		
		/**
		* Returns a textual representation of any amount of bytes
		*
		* @author		wesman20 (php.net)
		* @author		Jonas John
		* @version	0.3
		* @link			http://www.jonasjohn.de/snippets/php/readable-filesize.htm
		*
		* @return string A readable representation
		*/
		public function getHumanReadableSize() {
		
			$mod		= 1024;
			$units	= explode(' ','B KB MB GB TB PB');
			for ($i = 0; $size > $mod; $i++) {
				$size /= $mod;
			}
			
			return round($size, 2) . ' ' . $units[$i];	
		}
		
		public function setErrorCode($code) {
			$this->code = $code;
		}
		
		public function getErrorCode() {
			
			switch ($this->errorCode) {
				case 0:
					return UPLOAD_ERR_OK;
				case 1:
					return UPLOAD_ERR_INI_SIZE;
				case 2:
					return UPLOAD_ERR_FORM_SIZE;
				case 3:
					return UPLOAD_ERR_PARTIAL;
				case 4:
					return UPLOAD_ERR_NO_FILE;
				case 6:
					return UPLOAD_ERR_NO_TMP_DIR;
				case 7:
					return UPLOAD_ERR_CANT_WRITE;
				case 8:
					return UPLOAD_ERR_EXTENSION;
			}

		}
		
		public function getErrorMessage() {
			
			switch ($this->errorCode) {
				case 0:
					return 'The file was successfully uploaded';
				case 1:
					return 'The size exceeds upload_max_filesize set in php.ini';
				case 2:
					return 'The size exceeds MAX_FILE_SIZE set in the HTML form';
				case 3:
					return 'The file was only partially uploaded';
				case 4:
					return 'No file was uploaded';
				case 6:
					return 'No temporary directory was set';
				case 7:
					return 'Could not write to disk';
				case 8:
					return 'File upload stopped due to extension';
			}
			
		}
		
		public function setUploaded($uploaded) {
			$this->isUploaded = $uploaded;
		}
		
		public function isUploaded() {
			return $this->isUploaded;
		}
		
		public function getSuffix() {
			return pathinfo($this->getName(), PATHINFO_EXTENSION);
		}
		
		public function __toString() {
			throw new \Exception('Not yet implemented');
		}
	}

?>