<?php

	namespace Faultier\FileUploader;
	
	use Faultier\FileUploader\File;
	use Faultier\FileUploader\ConstraintInterface;
	use Faultier\FileUploader\FileUploaderException;
	
	class FileUploader {
	
		private $uploadDirectory;
		private $files;
		private $constraints;
		private $isMultiFileUpload;
		
		const NUMBER_OF_PHP_FILE_INFORMATION = 5;
		
		public function __construct($uploadDirectory, $constraints) {
			$this->setUploadDirectory($uploadDirectory);
			$this->setConstraints($constraints);
			
			$this->parseFilesArray();
		}
		
		# pramga mark setters / getters
		
		public function setUploadDirectory($uploadDirectory) {
			if ($this->checkUploadDirectory($uploadDirectory)) {
				$this->uploadDirectory = $uploadDirectory;
			}
		}
		
		public function getUploadDirectory() {
			return $this->uploadDirectory;
		}
		
		public function getFiles() {
			return array_values($this->files);
		}
		
		public function getFile($fieldName) {
			if ($this->isMultiFileUpload()) {
				throw new \BadMethodCallException('This is a multi file upload. Files cannot be distinguished by their field name.');
			} else {
				return $this->files[$fieldName];
			}
		}
		
		public function hasFiles() {
			return count($this->getFiles()) > 0;
		}
		
		public function setConstraints($constraints) {
		
			if (!is_array($constraints)) {
				$this->constraints = $this->parseConstraintString($constraints);
			} else {
				$this->constraints = $constraints;
			}
		}
		
		public function getConstraints() {
			return $this->constraints;
		}
		
		public function hasConstraints() {
			return !empty($this->constraints);
		}
		
		public function isMultiFileUpload() {
			return $this->isMultiFileUpload;
		}
		
		# pragma mark extended getters
		
		public function getFilesWithErrors() {
			
			$filesWithErrors = array();
			foreach ($this->getFiles() as $file) {
				if ($file->getErrorCode() != UPLOAD_ERR_OK) {
					$filesWithErrors[] = $file;
				}
			}
			
			return $filesWithErrors;
		}
		
		public function getFilesWithoutErrors() {
			
			$filesWithoutErrors = array();
			foreach ($this->getFiles() as $file) {
				if ($file->getErrorCode() == UPLOAD_ERR_OK) {
					$filesWithoutErrors[] = $file;
				}
			}
			
			return $filesWithoutErrors;
		}
		
		public function getUploadedFiles() {
		
			$files = array();
			foreach ($this->getFiles() as $file) {
				if ($file->isUploaded()) {
					$files[] = $file;
				}
			}
			
			return $files;
		}
		
		public function getNotUploadedFiles() {
			
			$files = array();
			foreach ($this->getFiles() as $file) {
				if (!$file->isUploaded()) {
					$files[] = $file;
				}
			}
			
			return $files;
		}
		
		public function getAggregatedFileSize() {
			
			$sum = 0;
			array_walk($this->getFiles(), function(&$n) use ($sum) {
				$sum += $n->getSize();
			});
			
			return $sum;
		}
		
		public function getReadableAggregatedFileSize() {
			return Faultier\FileUploader\Utilities::makeHumanReadableSize($this->getAggregatedFileSize());
		}
		
		# pragma mark parsing
		
		private function parseFilesArray() {
		
			$files = array();
			
			foreach ($_FILES as $field => $uploadedFile) {
			
				// multi file upload
				$this->isMultiFileUpload = is_array($uploadedFile['name']);
				$this->parseFilesArrayMultiUpload($field, $uploadedFile);
				break;
				
				$file = new File();
				$file->setOriginalName($uploadedFile['name']);
				$file->setTemporaryName($uploadedFile['tmp_name']);
				$file->setFieldName($field);
				$file->setMimeType($uploadedFile['type']);
				$file->setSize($uploadedFile['size']);
				$file->setErrorCode($uploadedFile['error']);
				$files[$field] = $file;
			}
			
			$this->files = $files;
		}
		
		private function parseFilesArrayMultiUpload($field, $uploadedFile) {
			
			$files = array();
			
			for ($i = 0; $i < FileUploader::NUMBER_OF_PHP_FILE_INFORMATION; $i++) { 
				
				$file = new File();
				$file->setOriginalName($uploadedFile['name'][$i]);
				$file->setTemporaryName($uploadedFile['tmp_name'][$i]);
				$file->setFieldName($field);
				$file->setMimeType($uploadedFile['type'][$i]);
				$file->setSize($uploadedFile['size'][$i]);
				$file->setErrorCode($uploadedFile['error'][$i]);
				$files[$field.$i] = $file;
				
			}
			
			$this->files = $files;
		}
		
		private function parseConstraintString($constraintString) {
			
			/*
			if (!is_null($criteria) && $criteria != "") {
				
				$crits = explode(' ', $criteria);
				
				foreach ($crits as $crit) {
					
					$mode	= strtoupper(substr(strrchr($crit, '|'), 1));
					$crit = str_replace('|'.$mode, '', $crit);
					$crit = explode('=', $crit);

					switch (strtolower($crit[0])) {
						case 'type':
							$this->setType(strtolower($crit[1]), $mode);
							break;
						case 'size':
							$this->setSize(strtolower($crit[1]), $mode);
							break;
					}
				}
				
				return true;
			} else {
				
				return false;
			}*/
			
		}
		
		# pragma mark constraints
		
		public function addConstraints(array $constraints) {
		
			foreach ($constraints as $constraint) {
				$this->addConstraint($constraint);
			}
		}
		
		public function addConstraint(ConstraintInterface $constraint) {
			$this->constraints[$constraint->getName()] = $constraint;
		}
		
		public function removeAllConstraints() {
			$this->constraints = array();
		}
		
		public function removeConstraint($name) {
			unset($this->constraints[$name]);
		}
		
		private function getConstraintsNotHolding(File $file) {
		
			$constraintsNotHolding = array();
			foreach ($this->getConstraints() as $constraint) {
				
				if (!$constraint->holds($file)) {
					$constraintsNotHolding[] = $constraint;
				}
			}
			
			return $constraintsNotHolding;
		}
		
		# pragma mark saving
	
		public function saveFile(File $file, $uploadDirectory = null) {
		
			// TODO better error management. error entity!
		
			// no file given
			if (is_null($file)) {
				throw new \InvalidArgumentException('The given file object is null');
				return false;
			}
			
			// file has upload errors
			if ($file->getErrorCode() != ERR_UPLOAD_OK) {
				$file->setUploaded(false);
				return false;;
			}
			
			// check and set upload directory
			if (!is_null($uploadDirectory)) {
				checkUploadDirectory($uploadDirectory);
			} else {
				$uploadDirectory = $this->getUploadDirectory();
			}
			
			$constraintsNotHolding = getConstraintsNotHolding($file);
			
			// constraints holding
			if (empty($constraintsNotHolding)) {
				
				// save file
				$filePath = $uploadDirectory.'/'.$file->getName();
				$isUploaded = @move_uploaded_file($file->getTemporaryName(), $filePath);
				
				if ($isUploaded) {
					$file->setFilePath($filePath);
					$file->setUploaded(true);
					return true;
				} else {
					$file->setUploaded(false);
					return false;
				}
				
				// constraint(s) not holding
			} else {
				$file->setUploaded(false);
				$file->setBrokenConstraints($constraintsNotHolding);
				return false;
			}
		}
		
		/**
		 * @return bool true, if all files were uploaded successful. otherwise false
		 */
		public function saveFiles($callable = null, $uploadDirectory = null) {
			
			$uploadSuccessfull = true;
			
			foreach ($this->getFiles() as $file) {
			
				// invoke the callable to let the caller manipulate the file
				if (!is_null($fileNameCallable) && !is_callable($fileNameCallable)) {
					throw new \InvalidArgumentException('The callable is not valid');
				} else if (!is_null($callable)) {
					call_user_func($callable, $file);
				}
				
				// set the file name if the caller has not done so
				if (is_null($callable) || (is_null($file->getName()) || $file->getName() == '')) {
					$file->setName($file->getTemporaryName());
				}
				
				$uploadSuccessfull = ($this->saveFile($file, $uploadDirectory) && $uploadSuccessfull);
			}
			
			return $uploadSuccessfull;
		}
		
		# pragma mark various
		
		public function checkUploadDirectory($uploadDirectory) {
		
			if (!file_exists($uploadDirectory)) {
				throw new \InvalidArgumentException('The given upload directory does not exist');
			}
		
			if (!is_dir($uploadDirectory)) {
				throw new \InvalidArgumentException('The given upload directory is not a directory');
			}
			
			if (!is_writable($uploadDirectory)) {
				throw new \InvalidArgumentException('The given upload directory is not writable');
			}
			
			return true;
		}
	}

?>