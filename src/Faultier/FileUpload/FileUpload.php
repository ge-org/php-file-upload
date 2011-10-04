<?php

	namespace Faultier\FileUpload;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\Constraint\ConstraintInterface;
	
	/**
	  * @link https://gist.github.com/1258900
	 */
	class FileUpload {
	
		const ERR_PHP_UPLOAD = 0;
		const ERR_FILESYSTEM = 1;
		const ERR_CONSTRAINT = 2;
		const ERR_MOVE_FILE = 3;
	
		private $files = array();
		private $constraints = array();
		private $constraintNamespaces = array();
		private $uploadDirectory = null;
		private $isMultiFileUpload = false;
		private $errorClosure = null;
		private $errorConstraintClosure = null;
		
		/**
		 * Creates a FileUpload instance.
		 *
		 * @param string  $uploadDirectory  The default directory to where files will be uploaded
		 * @param array   $constraints      An array describing the constraints to use
		 *
		 * @see registerConstraintNamespace
		 * @see setUploadDirectory
		 * @see setConstraints
		 * @see parseFilesArray
		 */
		public function __construct($uploadDirectory, array $constraints = array()) {
		
			$this->registerConstraintNamespace('Faultier\FileUpload\Constraint\SizeConstraint', 'size');
			$this->registerConstraintNamespace('Faultier\FileUpload\Constraint\TypeConstraint', 'type');
		
			$this->setUploadDirectory($uploadDirectory);
			$this->setConstraints($constraints);
			
			$this->parseFilesArray();
		}
		
		/**
		 * Registers the namespace and the alias of a constraint class.
		 *
		 * <code>
		 * $up->registerConstraintNamespace('My\Company\CoolConstraint', 'cool');
		 * </code>
		 *
		 * @param string  $namespace  The namespace of the constraint
		 * @param string  $alias      The alias of the constraint
		 *
		 * @throws \InvalidArgumentException  if the constraint class does not existor if it does not implement the {@link Faultier\FileUpload\Constraint\ConstraintInterface} interface
		 */
		public function registerConstraintNamespace($namespace, $alias) {
		
			$clazz = null;
			try {
				$clazz = new \ReflectionClass($namespace);
			} catch (\ReflectionException $e) {
				throw new \InvalidArgumentException(sprintf('The constraint class "%s" does not exist', $namespace));
			}
			
			if ($clazz->implementsInterface('Faultier\FileUpload\Constraint\ConstraintInterface')) {
				$this->constraintNamespaces[$alias] = $namespace;
			} else {
				throw new \InvalidArgumentException(sprintf('The class "%s" must implement "Faultier\FileUpload\Constraint\ConstraintInterface"', $namespace));
			}
		}
		
		/**
		 * Returns an array containing as key the alias of the constraint and as value its namespace.
		 *
		 * @return array The aliases and namespaces of the registered constraints
		 */
		public function getConstraintNamespaces() {
		  return $this->constraintNamespaces;
		}
		
		/**
		 * Returns the namespace of the constraint with the given alias.
		 * Returns null if the constraint has not been registered.
		 *
		 * @param string  $alias  The alias of the constraint
		 *
		 * @return string The namespace of the constraint or null
		 */
		public function resolveConstraintAlias($alias) {
		  return (isset($this->constraintNamespaces[$alias])) ? $this->constraintNamespaces[$alias] : null;
		}
		
		/**
		 * Sets the default upload directory.
		 *
		 * @param string  $uploadDirectory  The default upload directory
		 *
		 * @see checkUploadDirectory
		 */
		public function setUploadDirectory($uploadDirectory) {
			if ($this->checkUploadDirectory($uploadDirectory)) {
				$this->uploadDirectory = $uploadDirectory;
			}
		}
		
		/**
		 * Returns the default upload directory.
		 *
		 * @return string The default upload directory
		 */
		public function getUploadDirectory() {
			return $this->uploadDirectory;
		}
		
		/**
		 * Returns an array containing all files.
		 *
		 * @return array All files
		 */
		public function getFiles() {
			return array_values($this->files);
		}
		
		/**
		 * Returns the file that corresponds to a specifi HTML file tag.
		 *
		 * @param string  $fieldName  The name of the HTML file tag
		 *
		 * @return Faultier\FileUpload\File The desired file
		 *
		 * @throws \BadMethodCallException If the upload is a multi file upload {@link isMultiFileUpload}
		 */
		public function getFile($fieldName) {
			if ($this->isMultiFileUpload()) {
				throw new \BadMethodCallException('This is a multi file upload. Files cannot be distinguished by their field name.');
			} else if (!isset($this->files[$fieldName])) {
				throw new \InvalidArgumentException('The file does not exist');
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
					$clazz = new \ReflectionClass($options);
					if ($clazz->implementsInterface('Faultier\FileUpload\Constraint\ConstraintInterface')) {
						$this->addConstraint($options);
					}
				}
				
				// type and options string given
				else {
				
					if (!is_null($this->resolveConstraintAlias($type))) {
						$clazz = new \ReflectionClass($this->resolveConstraintAlias($type));
						$constraint = $clazz->newInstance();
						
						$constraint->parse($options);
						$this->addConstraint($constraint);
					} else {
						throw new \InvalidArgumentException(sprintf('The constraint "%s" has not been registered', $type));
					}
				}
				
			}
		}
		
		public function getConstraints() {
			return $this->constraints;
		}
		
		public function hasConstraints() {
			return !empty($this->constraints);
		}
		
		public function addConstraint(ConstraintInterface $constraint) {
			$this->constraints[] = $constraint;
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
			foreach ($this->getFiles() as $file) {
				$sum += $file->getSize();
			};
			
			return $sum;
		}
		
		public function getReadableAggregatedFileSize() {
			return $this->getHumanReadableSize($this->getAggregatedFileSize());
		}
		
		# pragma mark parsing
		
		private function parseFilesArray() {
			
			foreach ($_FILES as $field => $uploadedFile) {
			
				// multi file upload
				$this->isMultiFileUpload = is_array($uploadedFile['name']);
				if ($this->isMultiFileUpload()) {
					$this->parseFilesArrayMultiUpload($field, $uploadedFile);
				} else {
					$file = new File();
					$file->setOriginalName($uploadedFile['name']);
					$file->setTemporaryName($uploadedFile['tmp_name']);
					$file->setFieldName($field);
					$file->setMimeType($uploadedFile['type']);
					$file->setSize($uploadedFile['size']);
					$file->setErrorCode($uploadedFile['error']);
					$this->files[$field] = $file;
				}
			}
		}
		
		private function parseFilesArrayMultiUpload($field, $uploadedFile) {

			$numberOfFiles = count($uploadedFile['name']);
			for ($i = 0; $i < $numberOfFiles; $i++) {
			
				$file = new File();
				$file->setOriginalName($uploadedFile['name'][$i]);
				$file->setTemporaryName($uploadedFile['tmp_name'][$i]);
				$file->setFieldName($field);
				$file->setMimeType($uploadedFile['type'][$i]);
				$file->setSize($uploadedFile['size'][$i]);
				$file->setErrorCode($uploadedFile['error'][$i]);
				$file->setFieldName($field);
				
				$this->files[$field.$i] = $file;
			}
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
			}
			
			// file has upload errors
			if ($file->getErrorCode() != UPLOAD_ERR_OK) {
				$file->setUploaded(false);
				$this->callErrorClosure(FileUpload::ERR_PHP_UPLOAD, $file->getErrorMessage(), $file);
				return false;
			}
			
			// check and set upload directory
			if (!is_null($uploadDirectory)) {
				try {
					$this->checkUploadDirectory($uploadDirectory);
				} catch (\Exception $e) {
					$file->setUploaded(false);
					$this->callErrorClosure(FileUpload::ERR_FILESYSTEM, $e->getMessage(), $file);
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
			$filePath = $uploadDirectory . DIRECTORY_SEPARATOR . $file->getName();
			$isUploaded = @move_uploaded_file($file->getTemporaryName(), $filePath);
			
			if ($isUploaded) {
				$file->setFilePath($filePath);
				$file->setUploaded(true);
				return true;
			} else {
				$this->callErrorClosure(FileUpload::ERR_MOVE_FILE, sprintf('Could not move file "%s" to new location', $file->getName()), $file);
				$file->setUploaded(false);
				return false;
			}
		}
		
		/**
		 * @return bool true, if all files were uploaded successful. otherwise false
		 */
		public function save(\Closure $closure = null) {
			
			$uploadDirectory = null;
			$uploadSuccessful = true;
			
			foreach ($this->getFiles() as $file) {
			
				// invoke the callable to let the caller manipulate the file
				if (!is_null($closure)) {
					$uploadDirectory = $closure($file);
				}
				
				// set the file name if the caller has not done so
				if (is_null($closure) || (is_null($file->getName()) || $file->getName() == '')) {
					$file->setName($file->getTemporaryName());
				}
				
				$uploadDirectory = (is_null($uploadDirectory)) ? $this->getUploadDirectory() : $uploadDirectory;
				$uploadSuccessful = ($this->saveFile($file, $uploadDirectory) && $uploadSuccessful);
			}
			
			return $uploadSuccessful;
		}
		
		# pragma mark error handling
		
		public function error(\Closure $closure) {
			$this->errorClosure = $closure;
		}
		
		public function errorConstraint(\Closure $closure) {
			$this->errorConstraintClosure = $closure;
		}
		
		private function callErrorClosure($type, $message, File $file) {
			if (!is_null($this->errorClosure)) {
				call_user_func_array($this->errorClosure, array($type, $message, $file));
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