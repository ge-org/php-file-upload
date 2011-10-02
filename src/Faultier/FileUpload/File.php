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
			if (is_numeric($size)) {
				$this->size = $size;
			} else {
				throw new \InvalidArgumentException('The given size is not a number');
			}
		}
		
		public function getSize() {
			return $this->size;
		}
		
		public function setErrorCode($code) {
			switch ($code) {
				case 0:
					$this->errorCode = UPLOAD_ERR_OK;
					break;
				case 1:
					$this->errorCode = UPLOAD_ERR_INI_SIZE;
					break;
				case 2:
					$this->errorCode = UPLOAD_ERR_FORM_SIZE;
					break;
				case 3:
					$this->errorCode = UPLOAD_ERR_PARTIAL;
					break;
				case 4:
					$this->errorCode = UPLOAD_ERR_NO_FILE;
					break;
				case 6:
					$this->errorCode = UPLOAD_ERR_NO_TMP_DIR;
					break;
				case 7:
					$this->errorCode = UPLOAD_ERR_CANT_WRITE;
					break;
				case 8:
					$this->errorCode = UPLOAD_ERR_EXTENSION;
					break;
				default:
					throw new \InvalidArgumentException(sprintf('The error code "%s" is not valid', $code));
			}
		}
		
		public function getErrorCode() {
			return $this->errorCode;
		}
		
		public function getErrorMessage() {
			
			$msg = '';
			switch ($this->errorCode) {
				case UPLOAD_ERR_OK:
					$msg = 'The file was successfully uploaded';
					break;
				case UPLOAD_ERR_INI_SIZE:
					$msg = 'The size exceeds upload_max_filesize set in php.ini';
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$msg = 'The size exceeds MAX_FILE_SIZE set in the HTML form';
					break;
				case UPLOAD_ERR_PARTIAL:
					$msg = 'The file was only partially uploaded';
					break;
				case UPLOAD_ERR_NO_FILE:
					$msg = 'No file was uploaded';
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$msg = 'No temporary directory was set';
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$msg = 'Could not write to disk';
					break;
				case UPLOAD_ERR_EXTENSION:
					$msg = 'File upload stopped due to extension';
					break;
			}
			
			return $msg;
		}
		
		public function setUploaded($uploaded) {
			$this->isUploaded = (bool) $uploaded;
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
		
		public function getExtension() {
			return pathinfo($this->getName(), PATHINFO_EXTENSION);
		}
	}

?>