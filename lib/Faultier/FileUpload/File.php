<?php

	namespace Faultier\FileUpload;
	
	class File {
	
		private $name = null;
		private $originalName = null;
		private $temporaryName = null;
		private $fieldName = null;
		private $mimeType = null;
		private $size = 0; // in bytes
		private $errorCode = null;
		private $isUploaded = false;
		private $filePath = null;
		private $brokenConstraints = array();
		
		public function setName($name) {
			$this->name = $name;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function setOriginalName($name) {
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
		
		public function getHumanReadableSize() {
			return Faultier\FileUpload\Utilities::makeHumanReadableSize($this->size);
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
		
		public function setFilePath($filePath) {
			$this->filePath = $filePath;
		}
		
		public function getFilePath() {
			return $this->filePath;
		}
		
		public function setBrokenConstraints(array $constraints) {
			$this->brokenConstraints = $constraints;
		}
		
		public function getBrokenConstraints() {
			return $this->brokenConstraints;
		}
		
		public function getSuffix() {
			return pathinfo($this->getName(), PATHINFO_EXTENSION);
		}
	}

?>