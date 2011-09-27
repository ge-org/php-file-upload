<?php

	namespace Faultier\FileUpload;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\ConstraintInterface;
	use Faultier\FileUpload\FileUploadException;
	
	class FileUpload {
	
		private $uploadDirectory;
		private $files;
		private $constraints;
		private $isMultiFileUpload;
		private $errorClosure;
		private $errorConstraintClosure;
		
		const NUMBER_OF_PHP_FILE_INFORMATION = 5;
		
		public function __construct($uploadDirectory, array $constraints = array()) {
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
		
		public function setConstraints(array $constraints) {
		
			foreach ($constraints as $type => $options) {
				
				// an object has been given instead of an options string
				if (is_object($options)) {
					$clazz = new ReflectionClass($options);
					if ($clazz->implementsInterface('ConstraintInterface')) {
						$this->addConstraint($options);
					}
				}
				
				// type and options string given
				else {
					try {
						$clazz = new ReflectionClass(ucfirst($type).'Constraint');
						$constraint = $clazz->newInstance();
					} catch (\LogicException $e) {
						throw new \InvalidArgumentException(sprintf('The constraint type "%s" does not exist', $type));
					}
					
					$constraint->parse($options);
					$this->addConstraint($constraint);
				}
				
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
			return $this->getHumanReadableSize($this->getAggregatedFileSize());
		}
		
		# pragma mark parsing
		
		private function parseFilesArray() {
		
			$files = array();
			
			foreach ($_FILES as $field => $uploadedFile) {
			
				// multi file upload
				$this->isMultiFileUpload = is_array($uploadedFile['name']);
				if ($this->isMultiFileUpload()) {
					$this->parseFilesArrayMultiUpload($field, $uploadedFile);
					return;
				}
				
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
			
			for ($i = 0; $i < FileUpload::NUMBER_OF_PHP_FILE_INFORMATION; $i++) { 
				
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
		
		# pragma mark constraints
		
		public function removeConstraints() {
			$this->constraints = array();
		}
		
		# pragma mark saving
	
		public function saveFile(File $file, $uploadDirectory = null) {
		
			// no file given
			if (is_null($file)) {
				throw new \InvalidArgumentException('The given file object is null');
				return false;
			}
			
			// file has upload errors
			if ($file->getErrorCode() != ERR_UPLOAD_OK) {
				$file->setUploaded(false);
				$this->callErrorClosure(FileUploadException::ERR_PHP_UPLOAD, $file->getErrorMessage(), $file);
				return false;
			}
			
			// check and set upload directory
			if (!is_null($uploadDirectory)) {
				try {
					checkUploadDirectory($uploadDirectory);
				} catch (\Exception $e) {
					$file->setUploaded(false);
					$this->callErrorClosure(FileUploadException::ERR_FILESYSTEM, $e->getMessage(), $file);
					return false;
				}	
			} else {
				$uploadDirectory = $this->getUploadDirectory();
			}
			
			// check constraints
			foreach ($this->getConstraints() as $constraint) {
				if (!$constraint->holds()) {
					$file->setUploaded(false);
					$this->callConstraintClosure($constraint, $file);
					return false;
				}
			}

			// save file
			$filePath = $uploadDirectory . DIRECTORY_SEPERATOR . $file->getName();
			$isUploaded = @move_uploaded_file($file->getTemporaryName(), $filePath);
			
			if ($isUploaded) {
				$file->setFilePath($filePath);
				$file->setUploaded(true);
				return true;
			} else {
				$this->callErrorClosure(FileUploadException::ERR_MOVE_FILE, sprintf('Could not move file "%s" to new location', $file->getName()), $file);
				$file->setUploaded(false);
				return false;
			}
		}
		
		/**
		 * @return bool true, if all files were uploaded successful. otherwise false
		 */
		public function save(Closure $closure = null) {
			
			$uploadDirectory = null;
			$uploadSuccessful = true;
			
			foreach ($this->getFiles() as $file) {
			
				// invoke the callable to let the caller manipulate the file
				if (!is_null($callable)) {
					$uploadDirectory = $closure($file);
				}
				
				// set the file name if the caller has not done so
				if (is_null($callable) || (is_null($file->getName()) || $file->getName() == '')) {
					$file->setName($file->getTemporaryName());
				}
				
				$uploadDirectory = (is_null($uploadDirectory)) ? $this->getUploadDirectory() : $uploadDirectory;
				$uploadSuccessful = ($this->saveFile($file, $uploadDirectory) && $uploadSuccessful);
			}
			
			return $uploadSuccessful;
		}
		
		# pragma mark error handling
		
		public function error(Closure $closure) {
			$this->errorClosure = $closure;
		}
		
		public function errorConstraint(Closure $closure) {
			$this->errorConstraintClosure = $closure;
		}
		
		private function callErrorClosure($type, $message, File $file) {
			if (!is_null($this->errorClosure)) {
				$this->errorClosure($type, $message, $file);
			}
		}
		
		private function callConstraintClosure(ConstraintInterface $constraint, File $file) {
			if (!is_null($this->errorConstraintClosure)) {
				$this->errorConstraintClosure($constraint, $file);
			}
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
		
		/**
		* Returns a textual representation of any amount of bytes
		*
		* @author		wesman20 (php.net)
		* @author		Jonas John
		* @version	0.3
		* @link			http://www.jonasjohn.de/snippets/php/readable-filesize.htm
		*
		* @param $file	int	the size in bytes
		*
		* @return string A readable representation
		*/
		public function getHumanReadableSize($size) {
	
			$mod		= 1024;
			$units	= explode(' ','B KB MB GB TB PB');
			for ($i = 0; $size > $mod; $i++) {
				$size /= $mod;
			}
		
			return round($size, 2) . ' ' . $units[$i];
		}
	}

?>