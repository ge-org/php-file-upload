<?php

	namespace Faultier\FileUpload;
	
	use Faultier\FileUpload\File;
	use Faultier\FileUpload\UploadError;
	use Faultier\FileUpload\Constraint\ConstraintInterface;
	
	/**
	  * @link https://gist.github.com/1258900
	 */
	class FileUpload {
	
		private $files = array();
		private $constraints = array();
		private $constraintNamespaces = array();
		private $uploadDirectory = null;
		private $isMultiFileUpload = false;
		private $errorClosure = null;
		
		/**
		 * Creates a FileUpload instance.
		 * Registers the two built-in constraints.
		 *
		 * @param string  $uploadDirectory  The default directory to where files will be uploaded
		 * @param array   $constraints      An array describing the constraints to use
		 *
		 * @see registerConstraintNamespace
		 * @see setUploadDirectory
		 * @see addConstraints
		 */
		public function __construct($uploadDirectory, array $constraints = array()) {
		
			$this->registerConstraintNamespace('size', 'Faultier\FileUpload\Constraint\SizeConstraint');
			$this->registerConstraintNamespace('type', 'Faultier\FileUpload\Constraint\TypeConstraint');
		
			$this->setUploadDirectory($uploadDirectory);
			$this->addConstraints($constraints);
			
			$this->parseFilesArray();
		}
		
		/**
		 * Registers the namespace and the alias of a constraint class.
		 *
		 * <code>
		 * $up->registerConstraintNamespace('My\Company\FooConstraint', 'foo');
		 * </code>
		 *
		 * @param string  $alias      The alias of the constraint
		 * @param string  $namespace  The namespace of the constraint
		 *
		 * @throws \InvalidArgumentException  If the constraint class does not exist or if it does not implement the {@link Faultier\FileUpload\Constraint\ConstraintInterface} interface
		 */
		public function registerConstraintNamespace($alias, $namespace) {
		
			$clazz = null;
			try {
				$clazz = new \ReflectionClass($namespace);
			} catch (\ReflectionException $e) {
				throw new \InvalidArgumentException(sprintf('The constraint "%s" does not exist', $namespace));
			}
			
			if ($clazz->implementsInterface('Faultier\FileUpload\Constraint\ConstraintInterface')) {
				$this->constraintNamespaces[$alias] = $namespace;
			} else {
				throw new \InvalidArgumentException(sprintf('The "%s" must implement "Faultier\FileUpload\Constraint\ConstraintInterface"', $namespace));
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
		 * Sets the upload directory.
		 * Throws an exception if there occurrs any error.
		 *
		 * @param string  $uploadDirectory  The upload directory
		 *
		 * @throws \InvalidArgumentException If the upload directory does not exist. Code 0
		 * @throws \InvalidArgumentException If the upload directory is not a directory. Code 1
		 * @throws \InvalidArgumentException If the upload directory is not writable. Code 2
		 */
		public function setUploadDirectory($uploadDirectory) {
		
		  if (!file_exists($uploadDirectory)) {
				throw new \InvalidArgumentException('The given upload directory does not exist', 0);
			}
		
			if (!is_dir($uploadDirectory)) {
				throw new \InvalidArgumentException('The given upload directory is not a directory', 1);
			}
			
			if (!is_writable($uploadDirectory)) {
				throw new \InvalidArgumentException('The given upload directory is not writable', 2);
			}
			
			$this->uploadDirectory = $uploadDirectory;
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
		 * Returns the file that corresponds to a specific HTML file tag.
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
		
		/**
		 * Indicates whether any files have been uploaded.
		 *
		 * @return bool True if any files have been uploaded, otherwise false
		 */
		public function hasFiles() {
			return count($this->getFiles()) > 0;
		}
		
		/**
		 * Adds constraints. Either by alias and options string or by giving an object instance.
		 * The array must look like this:
		 * <code>
		 * array(
		 *  'alias' => 'options',
		 *   constraint instance
		 * );
		 * </code>
		 * 
		 * @param array $constraints  The constraints to add
		 *
		 * @throws \InvalidArgumentException If the given object does not implement {@link Faultier\FileUpload\Constraint\ConstraintInterface} or the alias has not been registered.
		 */
		public function addConstraints(array $constraints) {
		
			foreach ($constraints as $alias => $options) {
				
				// an object has been given instead of an options string
				if (is_object($options)) {
				
					$clazz = new \ReflectionClass($options);
					if ($clazz->implementsInterface('Faultier\FileUpload\Constraint\ConstraintInterface')) {
						$this->addConstraint($options);
					} else {
					  throw new \InvalidArgumentException('The given object does not implement the ConstraintInterface');
					}
				}
				
				// alias and options string given
				else {
				
					if (!is_null($this->resolveConstraintAlias($alias))) {
						$clazz = new \ReflectionClass($this->resolveConstraintAlias($alias));
						$constraint = $clazz->newInstance();
						$constraint->parse($options);
						$this->addConstraint($constraint);
					} else {
						throw new \InvalidArgumentException(sprintf('The constraint alias "%s" has not been registered', $alias));
					}
				}
				
			}
		}
		
		/**
		 * Returns all applied constraints.
		 *
		 * @return array All applied constraints
		 */
		public function getConstraints() {
			return $this->constraints;
		}
		
		/**
		 * Indicates whether any constraints will be applied.
		 *
		 * @return bool True, if there are any constraints, otherwise false
		 */
		public function hasConstraints() {
			return !empty($this->constraints);
		}
		
		/**
		 * Adds a constraint instance.
		 *
		 * @param Faultier\FileUpload\Constraint\ConstraintInterface  $constraint A constraint instance
		 */
		public function addConstraint(ConstraintInterface $constraint) {
			$this->constraints[] = $constraint;
		}
		
		/**
		 * Indicates whether the upload is a multi file upload.
		 * A multi file upload is a kind of uplaod where the HTML file tags are named all the same.
		 * Here is an example:
		 * <code>
		 * <input type="file" name="foo[]" />
		 * <input type="file" name="foo[]" />
		 * ...
		 * </code>
		 *
		 * @return bool True if it is a multi file upload, otherwise false
		 */
		public function isMultiFileUpload() {
			return $this->isMultiFileUpload;
		}
		
		/**
		 * Returns an array containing only files that have been successfully uploaded.
		 *
		 * @return array All files that have been successfully uploaded
		 */
		public function getUploadedFiles() {
		
			$files = array();
			foreach ($this->getFiles() as $file) {
				if ($file->isUploaded()) {
					$files[] = $file;
				}
			}
			
			return $files;
		}
		
		/**
		 * Returns an array containing only files that have not been successfully uploaded.
		 *
		 * @return array All files that have not been successfully uploaded
		 */
		public function getNotUploadedFiles() {
			return array_intersect($this->getFiles(), $this->getUploadedFiles());
		}
		
		/**
		 * Returns the aggregated file size of all files in bytes.
		 *
		 * @return int The aggregated file size of all files in bytes
		 */
		public function getAggregatedFileSize() {
			
			$sum = 0;
			foreach ($this->getFiles() as $file) {
				$sum += $file->getSize();
			};
			
			return $sum;
		}
		
		/**
		 * Returns the aggregated file size of all files in a human readable format.
		 *
		 * @return string The aggregated file size of all files in a human readable format
		 */
		public function getReadableAggregatedFileSize() {
			return $this->getHumanReadableSize($this->getAggregatedFileSize());
		}
		
		/**
		 * Parses the files array.
		 */
		private function parseFilesArray() {
			
			foreach ($_FILES as $field => $uploadedFile) {
			
				// multi file upload
				$this->isMultiFileUpload = is_array($uploadedFile['name']);
				if ($this->isMultiFileUpload()) {
					$this->parseFilesArrayMultiUpload($field, $uploadedFile);
				}
				
				// no multi file upload
				else {
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
		
		/**
		 * Parses the files array in case of a multi file upload.
		 */
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
		
		/**
		 * Removes all constraints.
		 */
		public function removeAllConstraints() {
			$this->constraints = array();
		}
	
	  /**
	   * Saves the specified file to the specified upload directory.
	   * If the specified upload directory is null the default directory will be used.
	   *
	   * @param Faultier\FileUpload\File  $file The file to upload
	   * @param string  $uploadDirectory  The upload directory to use
	   *
	   * @return bool True, if the file was uploaded successfully, otherwise false
	   */
		public function saveFile(File $file, $uploadDirectory = null) {
			
			// file has upload errors
			if ($file->getErrorCode() != UPLOAD_ERR_OK) {
				$file->setUploaded(false);
				$this->callErrorClosure(new UploadError(UploadError::ERR_PHP_UPLOAD, $file->getErrorMessage(), $file));
				return false;
			}
			
			// check and set upload directory
			$oldUploadDirectory = $this->getUploadDirectory();
			if (!is_null($uploadDirectory)) {
				try {
					$this->setUploadDirectory($uploadDirectory);
				} catch (\InvalidArgumentException $e) {
				  $this->setUploadDirectory($oldUploadDirectory);
					$file->setUploaded(false);
					$this->callErrorClosure(new UploadError(UploadError::ERR_FILESYSTEM, $e->getMessage(), $file));
					return false;
				}	
			}
			
			// check constraints
			foreach ($this->getConstraints() as $constraint) {
				if (!$constraint->holds($file)) {
				  $this->setUploadDirectory($oldUploadDirectory);
					$file->setUploaded(false);
					$this->callErrorClosure(new UploadError(UploadError::ERR_CONSTRAINT, 'Constraint did not hold', $file, $constraint));
					return false;
				}
			}

			// save file
			$filePath = $this->getUploadDirectory() . DIRECTORY_SEPARATOR . $file->getName();
			$isUploaded = $this->moveUploadedFile($file->getTemporaryName(), $filePath);
			
			if ($isUploaded) {
			  $this->setUploadDirectory($oldUploadDirectory);
				$file->setFilePath($filePath);
				$file->setUploaded(true);
				return true;
			} else {
			  $this->setUploadDirectory($oldUploadDirectory);
				$this->callErrorClosure(new UploadError(UploadError::ERR_PHP_UPLOAD, 'Could not move the file to the new location', $file));
				$file->setUploaded(false);
				return false;
			}
		}
		
		protected function moveUploadedFile($name, $directory) {
		  return @move_uploaded_file($name, $directory);
		}
		
		/**
		 * Saves all files and indicates whether all files have been uploaded successfully.
		 *
		 * @param \Closure  $closure A closure that will be passed the file object for manipulation. If the closure returns a string it will be used as the upload directory for the current file.
		 *
		 * @return bool True, if all files were uploaded successfully, otherwise false
		 *
		 * @see saveFile
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
				
				$uploadSuccessful = ($this->saveFile($file, $uploadDirectory) && $uploadSuccessful);
			}
			
			return $uploadSuccessful;
		}
		
		/**
		 * Sets the closure to be called in case any error occurrs while uploading.
		 *
		 * @param \Closure  $closure  The closure that will be called in case any error occurrs while uploading
		 */
		public function error(\Closure $closure) {
			$this->errorClosure = $closure;
		}
		
		/**
		 * Calls the error closure.
		 */
		private function callErrorClosure(UploadError $error) {
			if (!is_null($this->errorClosure)) {
				call_user_func($this->errorClosure, $error);
			}
		}
		
		/**
     * Return human readable sizes.
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.3.0
     * @link        http://aidanlister.com/2004/04/human-readable-file-sizes/
     * @param       int     $size        size in bytes
     * @param       string  $max         maximum unit
     * @param       string  $system      'si' for SI, 'bi' for binary prefixes
     * @param       string  $retstring   return string format
     */
		public function getHumanReadableSize($size, $max = null, $system = 'si', $retstring = '%01.2f %s') {
	
			// Pick units
      $systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
      $systems['si']['size']   = 1000;
      $systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
      $systems['bi']['size']   = 1024;
      $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];
    
      // Max unit to display
      $depth = count($sys['prefix']) - 1;
      if ($max && false !== $d = array_search($max, $sys['prefix'])) {
          $depth = $d;
      }
    
      // Loop
      $i = 0;
      while ($size >= $sys['size'] && $i < $depth) {
          $size /= $sys['size'];
          $i++;
      }
    
      return sprintf($retstring, $size, $sys['prefix'][$i]);
		}
	}

?>